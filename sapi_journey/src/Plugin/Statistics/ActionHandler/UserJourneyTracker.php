<?php

namespace Drupal\sapi_journey\Plugin\Statistics\ActionHandler;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\sapi\ActionHandlerInterface;
use Drupal\sapi\ActionHandlerBase;
use Drupal\sapi\ActionTypeInterface;
use Drupal\sapi_journey\Plugin\Statistics\ActionType\UserJourneyAction;

/**
 * This is a SAPI handler plugin is used to track user steps, throughout site,
 * and save in sapi_data information about user, session, URI and current step
 * in this session.
 *
 * @ActionHandler(
 *  id = "user_journey_tracker",
 *  label = "Track user steps on journey"
 * )
 *
 */
class UserJourneyTracker extends ActionHandlerBase implements ActionHandlerInterface, ContainerFactoryPluginInterface {

  /**
   * EntityTypeManager used to get entity storage for sapi_data items, which is
   * used to create and edit sapi_data items from tracking.
   *
   * @protected Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Current user for tracking
   *
   * @protected Drupal\Core\Session\AccountProxyInterface $currentUser;
   */
  protected $currentUser;

  /**
   * UserJourneyTracker constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, AccountProxyInterface $currentUser) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process(ActionTypeInterface $action){
    if (!($action instanceof UserJourneyAction)) {
      return;
    }

    /**
     * @var int $account
     *   the uid for the user that we will track
     */
    $account = $this->currentUser->id();

    /**
     * @var string $entity_type
     *   Entity type ID
     */
    $URI = $action->getURI();

    /**
     * @var string $sessionId
     *   ID of Session
     */
    $sessionId = $action->getSessionId();

    /**
     * @var EntityStorageInterface $sapiDataStorage
     */
    $sapiDataStorage = $this->entityTypeManager->getStorage('sapi_data');

    /**
     * Use EntityQuery to search for a matching session id
     * and if so take count of such data entries and create next
     * entry with added step number.
     *
     * @var array $results
     */
    $results = $sapiDataStorage->getQuery()
      ->condition('type', 'user_journey')
      ->condition('field_session_id', $sessionId)
      ->execute();

    if (count($results)>0) {
      $next_step = count($results) + 1;

      /** @var \Drupal\sapi_data\SAPIDataInterface $sapiData */
      $sapiData = $sapiDataStorage->create([
        'type' => 'user_journey',
        'name' => $sessionId,
        'field_session_id' => $sessionId,
        'field_user' => $account,
        'field_uri' => $URI,
        'field_step' => $next_step
      ]);

      if (!$sapiData->save()) {
        \Drupal::logger('sapi')->warning('Could not create SAPI data');
      }

    }
    else {
      /** Creating a new journey entity with default value for step number  */

      /** @var \Drupal\sapi_data\SAPIDataInterface $sapiData */
      $sapiData = $sapiDataStorage->create([
        'type' => 'user_journey',
        'name' => $sessionId,
        'field_session_id' => $sessionId,
        'field_user' => $account,
        'field_uri' => $URI,
        'field_step' => 1
      ]);

      if (!$sapiData->save()) {
        \Drupal::logger('sapi')->warning('Could not create SAPI data');
      }
    }

  }

}

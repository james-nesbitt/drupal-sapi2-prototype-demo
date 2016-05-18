<?php

namespace Drupal\sapi_demo\Plugin\StatisticsPlugin;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\sapi\Plugin\StatisticsPluginInterface;
use Drupal\sapi\Plugin\StatisticsPluginBase;
use Drupal\sapi\StatisticsItemInterface;

/**
 * This is a SAPI handler plugin used to track node views, and to count
 * colour frequencies for the user/date, by looking at the node->field_colours
 * value.
 *
 * @StatisticsPlugin(
 *  id = "article_color_tracker",
 *  label = "Track article colour views"
 * )
 *
 * @TODO currently route-id, item-action, and colour-field-name and logger id
 *   are all hardcoded strings.  These should be replaced or configured.
 */
class ArticleColorTracker extends StatisticsPluginBase implements StatisticsPluginInterface, ContainerFactoryPluginInterface {

  /**
   * Route match object as we only track on certain routes
   *
   * @protected \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   */
  protected $routeMatch;

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
   * ArticleColorTracker constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $routeMatch, EntityTypeManagerInterface $entityTypeManager, AccountProxyInterface $currentUser) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->routeMatch = $routeMatch;
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

      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process(StatisticsItemInterface $item){

    // We limit this action to only listening to one type of item action
    if ($item->getAction() != 'controller_view') {
      return;
    }

    // we will listen only to node canonical requests
    if ($this->routeMatch->getRouteName()!='entity.node.canonical') {
      return;
    }

    // we will not track anonymous user
    if ($this->currentUser->isAnonymous()) {
      return;
    }

    /** @var \Drupal\node\NodeInterface|null $currentNode */
    $currentNode = $this->routeMatch->getParameter('node');
    // parameter sanity check and color field check
    if (
        is_null($currentNode)
     || !($currentNode instanceof \Drupal\node\NodeInterface)
     || !$currentNode->hasField('field_colors')
    ) {
      return;
    }

    /**
     * @var int $color
     *   term tid for the colour that we will track
     */
    $color = $currentNode->get('field_colors')[0]->getValue()['target_id'];

    /**
     * @var int $account
     *   the uid for the user that we will track
     */
    $account = $this->currentUser->id();

    /**
     * @var int $date
     *  today's date, from a timestamp, but using only the day part.
     *
     * @see \Drupal\datetime\Plugin\Field\FieldType\DateTimeItem::generateSampleValue()
     */
    $date = gmdate(DATETIME_DATE_STORAGE_FORMAT, REQUEST_TIME);

    /**
     * We use the entity storage a few times, so we shortcut it here.
     * It is used to:
     *   - give a query to search for existing items
     *   - load an existing item if found
     *   - create a new item if needed
     *
     * @var EntityStorageInterface $sapiDataStorage
     */
    $sapiDataStorage = $this->entityTypeManager->getStorage('sapi_data');

    /**
     * Use EntityQuery to search for a matching color_frequency data
     * item "color-user-date".  This will tell us if we need to create
     * a new one, or update an existing one.
     *
     * @var array $results
     *
     *  retrieved using a \Drupal\Core\Entity\Query\QueryInterface which came
     *  from the Query Factory
     */
    $results = $sapiDataStorage->getQuery()
      ->condition('type', 'color_frequency')
      ->condition('field_color', $color)
      ->condition('field_user', $account)
      ->condition('field_date', $date)
      ->execute();

    if (count($results)>0) {
      /** Updating an existing tracking entity */

      /** @var int  $entity_id */
      $entity_id = array_keys($results)[0];

      /** @var \Drupal\sapi_data\SAPIDataInterface $sapiData */
      $sapiData = $sapiDataStorage->load($entity_id);

      /** @var \Drupal\Core\Field\FIeldItemInterface $fieldFrequency */
      $fieldFrequency =& $sapiData->get('field_frequency')[0];
      /** Increment the frequency value */
      $fieldFrequency->setValue(['value'=> $fieldFrequency->getValue()['value']+1]);

      if (!$sapiData->save()) {
        \Drupal::logger('sapi')->warning('Could not update SAPI data');
      }

    }
    else {
      /** Creating a new tracking entity */

      /** @var \Drupal\sapi_data\SAPIDataInterface $sapiData */
      $sapiData = $sapiDataStorage->create([
        'type' => 'color_frequency',
        'field_color' => $color,
        'field_user' => $account,
        'field_date' => $date,
        'field_frequency' => 1
      ]);

      if (!$sapiData->save()) {
        \Drupal::logger('sapi')->warning('Could not create SAPI data');
      }

    }

  }

}

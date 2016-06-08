<?php
namespace Drupal\sapi_user_journey\Plugin\Statistics\ActionType;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\sapi\ActionTypeBase;
use Drupal\sapi\Exception\MissingPluginConfiguration;

/**
 * @ActionType(
 *  id = "user_journey",
 *  label = "User viewed sites section"
 * )
 *
 * This action type holds information about an account interaction with site,
 * and keeps the URI, the account, and a string session ID value,
 * which can be retrieved by any handler.
 *
 * To Create pass
 *  $configuration = [
 *    'account' => \Drupal\Core\Session\AccountProxyInterface
 *    'uri' => string
 *    'sessionId' => string
 *  ];
 *
 */
class UserJourneyAction extends ActionTypeBase {

  /**
   * @var \Drupal\Core\Session\AccountProxyInterface $account
   */
  protected $account;

  /**
   * The URI of current "step" in journey
   *
   * @protected string $uri
   */
  protected $uri;

  /**
   * The journey Id(journey_id) that is kept in session.
   *
   * @protected string $sessionId
   */
  protected $sessionId;

  /**
   * UserJourneyAction constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param string $plugin_definition
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    if (!isset($configuration['account'])) {
      throw new MissingPluginConfiguration('Expected account in plugin generation.  None provided.');
    }
    if (!isset($configuration['uri'])) {
      throw new MissingPluginConfiguration('Expected string URI in plugin generation.  None provided.');
    }
    if (!isset($configuration['sessionId'])) {
      throw new MissingPluginConfiguration('Expected string session ID in plugin generation.  None provided.');
    }

    $this->account = $configuration['account'];
    $this->uri = $configuration['uri'];
    $this->sessionId = $configuration['sessionId'];
  }

  /**
   * {@inheritdoc}
   */
  public function describe() {
    return 'User step:[account:'.(($this->getAccount() instanceof AccountProxyInterface)?$this->getAccount()->getDisplayName().'('.$this->getAccount()->id().')':'none').'][URI:'.$this->uri.'][sessionId:'.$this->sessionId.']';
  }

  /**
   * Get the account who made a step.
   *
   * @return \Drupal\Core\Session\AccountProxyInterface|null
   */
  function getAccount() {
    return $this->account;
  }

  /**
   * Get the journey Id(journey_id) that is kept in session.
   *
   * @return string $sessionId
   */
  function getSessionId() {
    return $this->sessionId;
  }

  /**
   * Get the URI of current "step" in journey.
   *
   * @return string $URI
   */
  function getURI() {
    return $this->uri;
  }

}

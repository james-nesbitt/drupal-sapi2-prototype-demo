<?php
namespace Drupal\sapi_journey\Plugin\Statistics\ActionType;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\sapi\ActionTypeBase;
use Drupal\sapi\Exception\MissingPluginConfiguration;

/**
 * @ActionType(
 *  id = "user_journey",
 *  label = "User viewed sites section"
 * )
 *
 * This actiontype holds information about an account interaction with site,
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
   * The action taken on the entity
   *
   * @protected string $action
   */
  protected $uri;

  /**
   * Optionally an action mode
   *
   * For example: a view mode if that action if view or a form view mode
   *
   * @protected string $mode
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

    // save the account
    $this->account = $configuration['account'];
    // save the URI
    $this->uri = $configuration['uri'];
    // and the entity action
    $this->sessionId = $configuration['sessionID'];
  }

  /**
   * {@inheritdoc}
   */
  public function describe() {
    return 'User step:[account:'.(($this->getAccount() instanceof AccountProxyInterface)?$this->getAccount()->getDisplayName().'('.$this->getAccount()->id().')':'none').'][URI:'.$this->uri.'][sessionId:'.$this->sessionId.']';
  }

  /**
   * Get the account who viewed page
   *
   * @return \Drupal\Core\Session\AccountProxyInterface|null
   */
  function getAccount() {
    return $this->account;
  }

  /**
   * Get the session Id from current session.
   *
   * @return string sessionID
   */
  function getSessionId() {
    return $this->sessionId;
  }

  /**
   * Get the URI of current "step" in journey
   *
   * @return string URI
   */
  function getURI() {
    return $this->uri;
  }

}

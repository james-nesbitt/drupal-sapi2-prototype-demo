<?php

namespace Drupal\sapi_journey;

use Drupal\user\PrivateTempStoreFactory;
use Drupal\Core\Session\SessionManager;
use Drupal\Component\Utility\Crypt;

/**
 * Class JourneySessionHandler.
 *
 * @package Drupal\sapi_journey
 */
class JourneySessionHandler {

  /**
   * Drupal\user\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\user\PrivateTempStoreFactory
   */
  protected $userPrivateTempstore;

  /**
   * Drupal\Core\Session\SessionManager definition.
   *
   * @var \Drupal\Core\Session\SessionManager
   */
  protected $sessionManager;

  /**
   * Drupal\user\PrivateTempStore definition.
   *
   * @var \Drupal\user\PrivateTempStore
   */
  protected $store;

  /**
   * JourneySessionHandler constructor.
   *
   * @param \Drupal\user\PrivateTempStoreFactory $userPrivateTempstore
   * @param \Drupal\Core\Session\SessionManager $sessionManager
   */
  public function __construct(PrivateTempStoreFactory $userPrivateTempstore, SessionManager $sessionManager) {
    $this->userPrivateTempstore = $userPrivateTempstore;
    $this->sessionManager = $sessionManager;
    $this->store = $this->userPrivateTempstore->get('sapi_journey');
  }

  public function sessionStart() {
    $_SESSION['tracker_id'] = Crypt::randomBytesBase64();
    return $this->sessionManager->start();
  }

  public function isStarted() {
    return (isset($_SESSION['tracker_id']));
  }

  public function getSessionId() {
    return $_SESSION['tracker_id'];
  }
}

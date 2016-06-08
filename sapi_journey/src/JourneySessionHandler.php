<?php

namespace Drupal\sapi_user_journey;

use Drupal\Core\Session\SessionManager;
use Drupal\Component\Utility\Crypt;

/**
 * Class JourneySessionHandler.
 *
 * @package Drupal\sapi_user_journey
 */
class JourneySessionHandler {

  /**
   * SessionManager object manages user sessions.
   *
   * @var \Drupal\Core\Session\SessionManager
   */
  protected $sessionManager;


  /**
   * JourneySessionHandler constructor.
   *
   * @param \Drupal\Core\Session\SessionManager $sessionManager
   */
  public function __construct(SessionManager $sessionManager) {
    $this->sessionManager = $sessionManager;
  }

  public function sessionStart() {
    $_SESSION['journey_id'] = Crypt::randomBytesBase64();
    return $this->sessionManager->start();
  }

  public function isStarted() {
    return (isset($_SESSION['journey_id']));
  }

  public function getSessionId() {
    return $_SESSION['journey_id'];
  }
}

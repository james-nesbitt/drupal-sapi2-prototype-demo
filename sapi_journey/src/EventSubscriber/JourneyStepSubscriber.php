<?php

namespace Drupal\sapi_journey\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Drupal\Core\Session\AccountProxy;
use Drupal\sapi\Dispatcher;
use Drupal\sapi\ActionTypeManager;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class JourneyStepSubscriber.
 *
 * @package Drupal\sapi_journey
 */
class JourneyStepSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\Core\Session\AccountProxy definition.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * Drupal\sapi\Dispatcher definition.
   *
   * @var \Drupal\sapi\Dispatcher $sapiDispatcher
   */
  protected $sapiDispatcher;

  /**
   * Drupal\sapi\ActionTypeManager definition.
   *
   * @var \Drupal\sapi\ActionTypeManager $sapiActionTypeManager
   */
  protected $sapiActionTypeManager;

  /**
   * JourneyStepSubscriber constructor.
   *
   * @param \Drupal\Core\Session\AccountProxy $currentUser
   * @param \Drupal\sapi\Dispatcher $sapiDispatcher
   * @param \Drupal\Component\Plugin\PluginManagerInterface $SAPIActionTypeManager
   */
  public function __construct(AccountProxy $currentUser, Dispatcher $sapiDispatcher, ActionTypeManager $sapiActionTypeManager) {
    $this->currentUser = $currentUser;
    $this->sapiDispatcher = $sapiDispatcher;
    $this->sapiActionTypeManager = $sapiActionTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::VIEW][] = ['onJourneyStep',1];
    return $events;
  }

  /**
   * This method is called whenever the kernel.view event is
   * dispatched.
   *
   * @param \Symfony\Component\HttpKernel\Event\KernelEvent $event
   */
  public function onJourneyStep(Event $event) {
    try {
      if (!$this->journeySession->isStarted()) {
        $this->journeySession->sessionStart();
      }
      /** @var string $sessionId */
      $sessionId = $this->journeySession->getSessionId();
      /** @var string $uri */
      $uri = $event->getRequest()->getRequestUri();
//      drupal_set_message($uri, 'status', TRUE);
      $action = $this->sapiActionTypeManager->createInstance('user_journey', ['account'=> $this->currentUser,'uri'=> $uri,'sessionId'=> $sessionId]);
      if (!($action instanceof ActionTypeInterface)) {
        throw new \Exception('No user_journey plugin was found');
      }
      $this->sapiDispatcher->dispatch($action);
    } catch (\Exception $e) {
      watchdog_exception('sapi_journey', $e);
    }
  }

}

<?php

namespace Drupal\sapi_user_journey\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Drupal\Core\Session\AccountProxy;
use Drupal\sapi\Dispatcher;
use Drupal\sapi\ActionTypeManager;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\sapi_user_journey\JourneySessionHandler;
use Drupal\sapi\ActionTypeInterface;

/**
 * Class JourneyStepSubscriber.
 *
 * @package Drupal\sapi_user_journey
 */
class JourneyStepSubscriber implements EventSubscriberInterface {

  /**
   * JourneySessionHandler object that provides methods for dealing with
   * sessions in "journey" context.
   *
   * @var \Drupal\sapi_user_journey\JourneySessionHandler $journeySession
   */
  protected $journeySession;

  /**
   * Current user AccountProxy object.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * The Statistics API dispatcher.
   *
   * @var \Drupal\sapi\Dispatcher $sapiDispatcher
   */
  protected $sapiDispatcher;

  /**
   * The statistics action type plugin manager which will be used to create sapi
   * items to be passed to the dispatcher
   *
   * @var \Drupal\sapi\ActionTypeManager $sapiActionTypeManager
   */
  protected $sapiActionTypeManager;

  /**
   * JourneyStepSubscriber constructor.
   *
   * @param \Drupal\sapi_user_journey\JourneySessionHandler $journeySession
   * @param \Drupal\Core\Session\AccountProxy $currentUser
   * @param \Drupal\sapi\Dispatcher $sapiDispatcher
   * @param \Drupal\Component\Plugin\PluginManagerInterface $SAPIActionTypeManager
   */
  public function __construct(JourneySessionHandler $journeySession,AccountProxy $currentUser, Dispatcher $sapiDispatcher, ActionTypeManager $sapiActionTypeManager) {
    $this->journeySession = $journeySession;
    $this->currentUser = $currentUser;
    $this->sapiDispatcher = $sapiDispatcher;
    $this->sapiActionTypeManager = $sapiActionTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onJourneyStep',1];
    return $events;
  }

  /**
   * This method is called whenever the KernelEvents::REQUEST event is
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
      /** @var \Drupal\sapi\ActionTypeInterface $action */
      $action = $this->sapiActionTypeManager->createInstance('user_journey', ['account'=> $this->currentUser,'uri'=> $uri,'sessionId'=> $sessionId]);
      if (!($action instanceof ActionTypeInterface)) {
       throw new \Exception('No user_journey plugin was found');
      }
      $this->sapiDispatcher->dispatch($action);
    } catch (\Exception $e) {
      watchdog_exception('sapi_user_journey', $e);
    }
  }

}

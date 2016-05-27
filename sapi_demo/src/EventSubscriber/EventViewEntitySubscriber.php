<?php

namespace Drupal\sapi_demo\EventSubscriber;

use Drupal\Component\Plugin\PluginManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Drupal\Core\Session\AccountProxy;
use Drupal\sapi\Dispatcher;
use Drupal\Core\Routing\CurrentRouteMatch;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\sapi\ActionTypeInterface;

use Drupal\Core\Entity;

/**
 * Class EventViewEntitySubscriber.
 *
 * @package Drupal\sapi_demo
 */
class EventViewEntitySubscriber implements EventSubscriberInterface {

  /**
   * Drupal\Core\Session\AccountProxy definition.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * Drupal\sapi\Dispatcher definition.
   *
   * @var \Drupal\sapi\Dispatcher
   */
  protected $sapiDispatcher;

  /**
   * The statistics action type plugin manager which will be used to create sapi
   * items to be passed to the dispatcher
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface $sapiActionTypeManager
   */
  protected $sapiActionTypeManager;

  /**
   * Drupal\Core\Routing\CurrentRouteMatch definition.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * Constructor.
   */
  public function __construct(AccountProxy $currentUser, Dispatcher $sapiDispatcher, PluginManagerInterface $sapiActionTypeManager, CurrentRouteMatch $currentRouteMatch) {
    $this->currentUser = $currentUser;
    $this->sapiDispatcher = $sapiDispatcher;
    $this->sapiActionTypeManager = $sapiActionTypeManager;
    $this->currentRouteMatch = $currentRouteMatch;
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    // Priority is set to 1 to avoid this listener from being stopped by other
    // listeners.
    // @see ContainerAwareEventDispatcher::dispatch()
    $events[KernelEvents::VIEW][] = ['onEventView', 1];
    return $events;
  }

  /**
   * Informs Statistics API dispatcher when controller outputs a value which is
   * not a Response instance.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   */
  public function onEventView(Event $event) {
    try {
      /** @var $routeData[] An array of strings containing consecutive parts of route name. */
      $routeData = explode('.',$this->currentRouteMatch->getRouteName());

      if (in_array('canonical', $routeData)) {
        /** @var \Drupal\Core\Entity\EntityInterface $entity */
        $entity = $this->currentRouteMatch->getParameter($routeData[1]);
        /** @var string $mode String containing Display mode. */
        $mode = $event->getControllerResult()['#view_mode'];
        /** @var \Drupal\sapi\ActionTypeInterface $action */
        $action = $this->sapiActionTypeManager->createInstance('entity_interaction', ['account'=> $this->currentUser,'entity'=> $entity,'action'=> 'View','mode'=> $mode]);
        if (!($action instanceof ActionTypeInterface)) {
          throw new \Exception('No entity_interaction plugin was found');
        }
        $this->sapiDispatcher->dispatch($action);
      }
    } catch (\Exception $e) {
      watchdog_exception('sapi_demo', $e);
    }
  }

}

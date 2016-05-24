<?php

namespace Drupal\sapi_demo\EventSubscriber;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\sapi\Dispatcher;
use Drupal\sapi\ActionTypeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class RequestViewEventSubscriber
 */
class RequestViewEventSubscriber implements EventSubscriberInterface {

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
   * @var \Drupal\Component\Plugin\PluginManagerInterface $SAPIActionTypeManager
   */
  protected $SAPIActionTypeManager;


  /**
   * RequestViewEventSubscriber constructor.
   *
   * @param \Drupal\sapi\Dispatcher $sapiDispatcher
   * @param \Drupal\Component\Plugin\PluginManagerInterface $SAPIActionTypeManager
   */
  public function __construct(Dispatcher $sapiDispatcher, PluginManagerInterface $SAPIActionTypeManager) {
    $this->sapiDispatcher = $sapiDispatcher;
    $this->SAPIActionTypeManager = $SAPIActionTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    // Priority is set to 1 to avoid this listener from being stopped by other
    // listeners.
    // @see ContainerAwareEventDispatcher::dispatch()
    $events[KernelEvents::VIEW][] = ['onRequestView', 1];
    return $events;
  }

  /**
   * Informs Statistics API dispatcher when controller outputs a value which is
   * not a Response instance.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent $event
   */
  public function onRequestView(GetResponseForControllerResultEvent $event) {
    try {
      /** @var \Drupal\sapi\ActionTypeInterface $action */
      $action = $this->SAPIActionTypeManager->createInstance('tagged', [ 'tag'=>'sapi_demo_nodeview' ]);
      if (!($action instanceof ActionTypeInterface)) {
        throw new \Exception('No tagged plugin was found');
      }

      $this->sapiDispatcher->dispatch($action);
    } catch (\Exception $e) {
      watchdog_exception('sapi_demo', $e);
    }
  }

}

<?php

namespace Drupal\sapi_demo\EventSubscriber;

use Drupal\sapi\Dispatcher;
use Drupal\sapi\StatisticsItem;
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
   * RequestViewEventSubscriber constructor.
   *
   * @param \Drupal\sapi\Dispatcher $sapiDispatcher
   */
  public function __construct(Dispatcher $sapiDispatcher) {
    $this->sapiDispatcher = $sapiDispatcher;
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
      $this->sapiDispatcher->dispatch(new StatisticsItem('controller_view', ''));
    } catch (\Exception $e) {
      watchdog_exception('sapi_demo', $e);
    }
  }

}

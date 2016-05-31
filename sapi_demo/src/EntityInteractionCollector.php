<?php

namespace Drupal\sapi_demo;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\sapi\ActionTypeInterface;
use Drupal\sapi\Dispatcher;
use Drupal\sapi\ActionTypeManager;
use Drupal\Core\Entity;

/**
 * Class EntityInteractionCollector.
 *
 * @package Drupal\sapi_demo
 */
class EntityInteractionCollector {

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
   * Drupal\sapi\ActionTypeManager definition.
   *
   * @var \Drupal\sapi\ActionTypeManager
   */
  protected $sapiActionTypeManager;

  /**
   * EntityInteractionCollector constructor.
   */
  public function __construct(AccountProxy $currentUser, Dispatcher $sapiDispatcher, ActionTypeManager $sapiActionTypeManager) {
    $this->currentUser = $currentUser;
    $this->sapiDispatcher = $sapiDispatcher;
    $this->sapiActionTypeManager = $sapiActionTypeManager;
  }

  /**
   * Initiates Drupal\sapi\ActionType plugin and hands it to dispatcher
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param string $operation Type of operation made on entity.
   */
  function actionTypeTrigger(EntityInterface $entity, $operation){
    try {
      /** @var \Drupal\sapi\ActionTypeInterface $action */
      $action = $this->sapiActionTypeManager->createInstance('entity_interaction', ['account'=> $this->currentUser,'entity'=> $entity,'action'=> $operation,'mode'=> '']);
      if (!($action instanceof ActionTypeInterface)) {
        throw new \Exception('No entity_interaction plugin was found');
      }
      $this->sapiDispatcher->dispatch($action);
    } catch (\Exception $e) {
      watchdog_exception('sapi_demo', $e);
    }
  }

}

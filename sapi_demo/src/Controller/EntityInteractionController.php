<?php

namespace Drupal\sapi_demo\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\sapi\DispatcherInterface;
use Drupal\sapi\ActionTypeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;



/**
 * Class EntityInteractionController.
 *
 * @package Drupal\sapi_demo\Controller
 */
class EntityInteractionController extends ControllerBase implements ContainerInjectionInterface{

  /**
   * @var \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   Used to retrieve POST variables to create Action data
   */
  protected $requestStack;

  /**
   * @var \Drupal\sapi\DispatcherInterface $sapiDispatcher
   *  use to receive action items
   */
  protected $sapiDispatcher;

  /**
   * The statistics action type plugin manager which will be used to create sapi
   * items to be passed to the dispatcher
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface $sapi_action_type_manager
   */
  protected $sapi_action_type_manager;

  /**
   * Symfony Container which we may use to convert arguments to services
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface $container
   */
  protected $container;

  /**
   * EntityInteractionController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   * @param \Drupal\sapi\DispatcherInterface $sapiDispatcher
   * @param \Drupal\Component\Plugin\PluginManagerInterface $sapi_action_type_manager
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   */
  public function __construct(RequestStack $requestStack, DispatcherInterface $sapiDispatcher, PluginManagerInterface $sapi_action_type_manager, ContainerInterface $container) {
    $this->requestStack = $requestStack;
    $this->sapiDispatcher = $sapiDispatcher;
    $this->sapi_action_type_manager = $sapi_action_type_manager;
    $this->container = $container;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('sapi.dispatcher'),
      $container->get('plugin.manager.sapi_action_type'),
      $container
    );
  }
  
  /**
   * Action.
   *
   * @return string
   *   Return Action string.
   */
  public function action($action) {
    
    if (empty($action)) {
      throw new BadRequestHttpException('Action Parameter was not defined.');
    }

    // Get current request.
    $request = $this->requestStack->getCurrentRequest();
    $configuration = $request->get('action');
    $configurations = explode(":", $configuration);
    //I splited string into peaces. Delimiter is ":"(my own customisation in sapi_demo.js file)



    $actions = $this->sapi_action_type_manager->createInstance('entity_interaction', $configurations);
    // never get further, because i get an error about "Expected string action in plugin generation. None provided."


  /*  if (!($actions instanceof ActionTypeInterface)) {
      throw new BadRequestHttpException('Action Parameter does not correspond to any know action type.');
    }*/


    try {

      // Send to SAPI dispatcher.
      //$this->sapiDispatcher->dispatch($action);

      \Drupal::logger('js_contr')->notice($configuration);
      //this logs $configuration set(not array)

      return new JsonResponse('OK', 200);
    } catch (\Exception $e) {
      throw new HttpException(500, 'Internal Server Error', $e);
    }
  }

}

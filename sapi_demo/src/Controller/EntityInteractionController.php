<?php

namespace Drupal\sapi_demo\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EntityInteractionController.
 *
 * @package Drupal\sapi_demo\Controller
 */
class EntityInteractionController extends ControllerBase {

  /**
   * @var \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   Used to retrieve POST variables to create Action data
   */
  protected $requestStack;

  /**
   * EntityInteractionController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack

   */
  public function __construct(RequestStack $requestStack, ContainerInterface $container) {
    $this->requestStack = $requestStack;
    $this->container = $container;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
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


    try {

      \Drupal::logger('js_contr')->notice($configuration);

      return new Response('OK', 200);
    } catch (\Exception $e) {
      throw new HttpException(500, 'Internal Server Error', $e);
    }
  }

}

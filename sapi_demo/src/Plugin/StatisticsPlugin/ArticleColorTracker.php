<?php

namespace Drupal\sapi_demo\Plugin\StatisticsPlugin;

use Drupal\sapi\Plugin\StatisticsPluginInterface;
use Drupal\sapi\Plugin\StatisticsPluginBase;
use Drupal\sapi\StatisticsItemInterface;

/**
 * @StatisticsPlugin(
 *  id = "article_color_tracker",
 *  label = "Track article colour views"
 * )
 */
class ArticleColorTracker extends StatisticsPluginBase implements StatisticsPluginInterface {

  /**
   * EntityFieldQuery used to load the SAPI Data Items
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityFieldQuery;

  /**
   * {@inheritdoc}
   */
  public function process(StatisticsItemInterface $item){

    /** @var \Drupal\Core\Routing\CurrentRouteMatch $route */
    $route = \Drupal::routeMatch();
    if ($route->getRouteName()!='entity.node.canonical') {
      return;
    }

    /** @var \Drupal\Core\Session\AccountProxyInterface $currentUser */
    $currentUser = \Drupal::currentUser();
    // we will not track anonymous user
    if ($currentUser->isAnonymous()) {
      drupal_set_message("ANON USER");
      return;
    }

    /** @var \Drupal\node\NodeInterface|null $currentNode */
    $currentNode = $route->getParameter('node');
    // parameter sanity check and color field check
    if (
        is_null($currentNode)
     || !$currentNode->hasField('field_colours')
    ) {
      return;
    }

    /**
     * @var int $color
     *   Term tid for the colour that we will track
     */
    $color = $currentNode->get('field_colors')[0]->getValue()['target_id'];

    /**
     * @var int $account
     *   the uid for the user that we will track
     */
    $account = $currentUser->id();

    /**
     * @var int $date
     *  today's date, from a timestamp, but using only the day part.
     *
     * @note this requires the datetime module, but does not enforce that.
     */
    $date = gmdate(DATETIME_DATE_STORAGE_FORMAT, REQUEST_TIME);


    drupal_set_message("TRACKING [date:$date][account:$account][color:$color]");

    /**
     * @var array $results
     *  retrieved using a \Drupal\Core\Entity\Query\QueryInterface
     */
    $results = \Drupal::entityQuery('sapi_data')
      ->condition('type', 'color_frequency')
      ->condition('field_color', $color)
      ->condition('field_user', $account)
      ->condition('field_date', $date)
      ->execute();

    if (count($results)>0) {
      $entity_id = array_keys($results)[0];

      /** @var \Drupal\sapi_data\SAPIDataInterface $sapiData */
      $sapiData = \Drupal
        ::entityTypeManager()
        ->getStorage('sapi_data')
        ->load($entity_id);

      /** @var \Drupal\Core\Field\FIeldItemInterface $fieldFrequency */
      $fieldFrequency =& $sapiData->get('field_frequency')[0];
      $fieldFrequency->setValue(['value'=> $fieldFrequency->getValue()['value']+1]);

      if (!$sapiData->save()) {
        \Drupal::logger('sapi')->warning('Could not update SAPI data');
      }

    }
    else {

      /** @var \Drupal\sapi_data\SAPIDataInterface $sapiData */
      $sapiData = \Drupal
        ::entityTypeManager()
        ->getStorage('sapi_data')
        ->create([
            'type' => 'color_frequency',
            'field_color' => $color,
            'field_user' => $account,
            'field_date' => $date,
            'field_frequency' => 1
          ]);

      if (!$sapiData->save()) {
        \Drupal::logger('sapi')->warning('Could not create SAPI data');
      }

    }

  }

}

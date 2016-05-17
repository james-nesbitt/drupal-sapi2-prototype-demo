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

    /**
     * @var int $color
     *   Term tid for the colour that we will track
     */
    $color = 1;
    /**
     * @var int $account
     *   the uid for the user that we will track
     */
    $account = 1;
    /**
     * @var int $date
     *  today's date, as a timestamp, but set to this morning.
     */
    $date = REQUEST_TIME;


    /**  @var \Drupal\Core\Entity\Query\QueryFactory $queryFactory */
    $queryFactory = \Drupal::service('entity.query');

    /**
     * @var array $results
     *  retrieved using a \Drupal\Core\Entity\Query\QueryInterface
     */
    $results = $queryFactory->get('sapi_data')
      ->condition('type', 'color_frequency')
      ->condition('field_color', $color)
      ->condition('field_user', $account)
//      ->condition('field_date', $date)
      ->execute();

    /** @var \Drupal\Core\Entity\EntityStorageInterface $entityStorageManager */
    $entityStorageManager = \Drupal::service('entity_type.manager')->getStorage('sapi_data');

    /** @var \Drupal\sapi_data\SAPIDataInterface $sapiData */

    if (count($results)>0) {
      $entity_id = reset(array_keys($results));
      $sapiData = $entityStorageManager->load($entity_id);

      $sapiData->field_frequency[0]->setValue(['value'=> $sapiData->field_frequency[0]->getValue()['value']+1]);
      $sapiData->save();

    }
    else {

      $entityStorageManager->create([
        'type' => 'color_frequency',
        'field_color' => $color,
        'field_user' => $account,
        'field_date' => $date,
        'field_frequency' => 1
      ])->save();

    }

  }

}

<?php

namespace Drupal\formcustomtemplate\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Example module.
 */
class ExampleController extends ControllerBase  {


  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function build() {
    $build = [];
    $build[] = ['#markup' => '<h2>custom form:</h2>'];
    $build['custom_form'] =  \Drupal::formBuilder()->getForm('Drupal\formcustomtemplate\Form\ExampleForm');

    return $build;
  }

  /**
   * Get node data for page.
   */
  private function getData() {
    static $data;


    if (!empty($data)) {
      return $data;
    }


    if (!empty($_GET['condition'])) {
      $condition = $_GET['condition'];
    }

    $cid = 'lifecenter_binder';

    if (!empty($condition)) {
      $cid = $cid . ':' . $condition;
    }

    $data = NULL;
    if (0 && $cache = \Drupal::cache()->get($cid)) {
      $data = $cache->data;
    }
    else {

      $query = $this->database->select('node__field_key_topics', 't')
        ->fields('t', array('field_key_topics_target_id'))
        ->condition('t.bundle', $this->lifeCenterContentTypes, 'IN');

      $query->join('node_field_data', 'n', 'n.nid = t.entity_id');
      $query->condition('n.status', 1);

      $query->addExpression('COUNT(t.field_key_topics_target_id)', 'count');

      if (!empty($condition)) {
        $query->join('node__field_conditions', 'c', 'c.entity_id = t.entity_id');
        $query->condition('c.field_conditions_target_id', $condition);
      }

      $query->groupBy('t.field_key_topics_target_id');
      $query->orderBy('count', 'DESC');

      $results = $query->execute();

      $data = [];
      foreach ($results as $r) {
        $nids = $this->getGroupNids($r->field_key_topics_target_id);
        $data[$r->field_key_topics_target_id] = [
          'count' => $r->count,
          'nids' => $nids,
        ];
      }

      \Drupal::cache()->set($cid, $data);
    }

    return $data;
  }

  /**
   * Returns the nids of the first four nodes for each
   * group in the binder.
   *
   * @param $tid
   *   Term id of the lifecenter_key_topics vocabulary.
   * @return array
   *   Array of node nids.
   */
  private function getGroupNids($tid) {
    $limit = 4;

    $query = $this->database->select('node__field_key_topics', 't')
      ->fields('t', array('field_key_topics_target_id'))
      ->condition('t.bundle', $this->lifeCenterContentTypes, 'IN');

    $query->join('node_field_data', 'n', 'n.nid = t.entity_id');
    $query->condition('n.status', 1);

    if (!empty($condition)) {
      $query->join('node__field_conditions', 'c', 'c.entity_id = t.entity_id');
      $query->condition('c.field_conditions_target_id', $condition);
    }

    $query->join('node__field_post_date', 'pd', 'pd.entity_id = t.entity_id');
    $query->orderBy('pd.field_post_date_value', 'DESC');

    $query->range(0, $limit);

    return $query->execute()->fetchCol();
  }

  /**
   * Get groups based on current selection.
   */
  private function getGroups($data) {
    $build = [];
    foreach ($data as $tid => $group_data) {
      $term = Term::load($tid);

      $build[$tid] = [
        '#theme' => 'lifecenter_binder_group',
        '#tid' => ['#markup' => $tid],
        '#title' => ['#markup' => $term->label()],
        '#items' => $this->renderItems($group_data['nids']),
        '#count' => ['#markup' => $group_data['count']],
        '#more_link' => ['#markup' => 'http://www.google.com/'],
      ];
    }
    return $build;
  }

  /**
   * Get key topics build array.
   */
  private function getKeyTopics() {

    $data = $this->getData();

    $build = array(
      '#type' => 'container',
      '#attributes' => array('class' => array(
        'key-topics'
      ))
    );

    foreach ($this->keyTopics as $key => $value) {
      if (!empty($data[$value->tid])) {
        $build[$key] = ['#markup' => '<p><a href="#tid-' . $value->tid . '">' . $value->name . '</a></p>'];
      }
      else {
        $build[$key] = ['#markup' => '<p><span>' . $value->name . '</span></p>'];
      }
    }

    return $build;
  }

  /**
   * Get nodes as render arrays in view mode "card".
   *
   * @param array $nids
   *    Nodes to render.
   * @param string $view_mode
   *    View mode to use to render.
   */
  public function renderItems($nids) {
    $entity_type = 'node';
    $view_mode = 'search_result';
    $entity_storage = \Drupal::entityTypeManager()->getStorage($entity_type);
    $items = [];
    if ($entities = $entity_storage->loadMultiple($nids)) {
      $view_builder = \Drupal::entityTypeManager()->getViewBuilder($entity_type);
      $items = $view_builder->viewMultiple($entities, $view_mode);
    }
    return $items;
  }

}

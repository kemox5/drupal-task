<?php

namespace Drupal\event_management\Controller;

use Drupal\event_management\Form\EventConfigs;

class EventController
{

  public $id;

  public $getShowPageTitle = '';

  public function list()
  {
    $content = [];

    $config = EventConfigs::getConfig();
    $show_past_events = $config['show_past_events'] ?? false;
    $number_of_events_to_list = $config['number_of_events_to_list'] ?? 10;


    $query = \Drupal::database()->select('events', 'm');
    $query->fields('m', ['id', 'title', 'image', 'start_date', 'end_date', 'description', 'is_published', 'category']);

    if (!$show_past_events) {
      $query->condition('end_date', date('Y-m-d'), '>=');
    }

    $results = $query
      ->range(0,  $number_of_events_to_list)
      ->condition('is_published', 1)
      ->orderBy('id', 'desc')
      ->execute()->fetchAll();

    $content['events'] = $results;

    return [
      '#theme' => 'event-listing',
      '#content' => $content,
    ];
  }

  public function show($id = null)
  {
    $content = [];
    $query = \Drupal::database()->select('events', 'm');
    $query->fields('m', ['id', 'title', 'image', 'start_date', 'end_date', 'description', 'is_published', 'category']);
    $result = $query
      ->condition('id', $id)
      ->execute()->fetch();

    if (!$result) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }

    $content['event'] = $result;
    return [
      '#theme' => 'event-show',
      '#content' => $content,
      '#title' => $result->title,
    ];
  }
}

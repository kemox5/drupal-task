<?php

namespace Drupal\event_management\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\event_management\Form\EventConfigs;

/**
 * Provides a 'Event' Block.
 *
 * @Block(
 *   id = "event_block",
 *   admin_label = @Translation("Event block"),
 *   category = @Translation("Event block"),
 * )
 */
class EventBlock extends BlockBase
{

      /**
       * {@inheritdoc}
       */
      public function build()
      {

            $config = EventConfigs::getConfig();
            $show_past_events = $config['show_past_events'] ?? false;

            $query = \Drupal::database()->select('events', 'm');
            $query->fields('m', ['id', 'title', 'image']);

            if (!$show_past_events) {
                  $query
                        ->condition('end_date', date('Y-m-d'), '>=');
            }

            $results = $query
                  ->condition('is_published', 1)
                  ->orderBy('id', 'desc')
                  ->range(0,  5)
                  ->execute()->fetchAll();

            return [
                  '#theme' => 'event_block',
                  '#data' => $results,
            ];
      }
}

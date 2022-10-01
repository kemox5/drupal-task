<?php

namespace Drupal\event_management\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Event
 * @package Drupal\event_management\Form
 */
class EventConfigs extends FormBase
{

  public static function getConfig()
  {
    $config = [];

    $query = \Drupal::database()->select('config', 'c')
      ->fields('c', ['name', 'data'])
      ->condition('name', 'event_management.config')
      ->execute()->fetch();

    if ($query)
      $config = (array) json_decode($query->data);

    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'event_config_page';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $config = self::getConfig();

    $form['show_past_events'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show past events'),
      '#default_value' => $config['show_past_events'] ?? 1,
    ];


    $form['number_of_events_to_list'] = [
      '#type' => 'number',
      '#title' => $this->t('What is the number of events to list in listing page?'),
      '#default_value' => $config['number_of_events_to_list'] ?? 50,
    ];


    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  public function addLog($description)
  {
    $database = Drupal::database();
    $user = Drupal::currentUser()->getAccount();
    $userId = $user->id();
    $userName = $user->getAccountName();

    $log = [
      'user_id' => $userId,
      'datetime' => date('Y-m-d H:i:s'),
      'description' =>  $userName . ' ' . $description
    ];

    $database->insert('events_config_log')
      ->fields($log)
      ->execute();
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $submitted_values = $form_state->cleanValues()->getValues();

    $data = [
      'name' => 'event_management.config',
      'data' => json_encode(
        [
          'show_past_events' => $submitted_values['show_past_events'],
          'number_of_events_to_list' => $submitted_values['number_of_events_to_list']
        ]
      )
    ];

    $database = \Drupal::database();

    $config = self::getConfig();

    if ($config) {

      if ($config['show_past_events'] !=  $submitted_values['show_past_events']) {
        $this->addLog('updated show_past_events config to ' . $submitted_values['show_past_events']);
      }

      if ($config['number_of_events_to_list'] !=  $submitted_values['number_of_events_to_list']) {
        $this->addLog('updated number_of_events_to_list config to ' . $submitted_values['number_of_events_to_list']);
      }

      $database->update('config')
        ->condition('name', 'event_management.config')
        ->fields($data)
        ->execute();
    } else {
      $database->insert('config')
        ->fields($data)
        ->execute();
    }
    drupal_flush_all_caches();
    $messenger = \Drupal::service('messenger');
    $messenger->addMessage($this->t('Your new configuration has been saved.'));
  }
}

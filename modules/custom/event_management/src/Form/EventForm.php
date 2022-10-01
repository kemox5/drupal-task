<?php

namespace Drupal\event_management\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class MydataForm.
 *
 * @package Drupal\mydata\Form
 */
class EventForm extends FormBase
{
  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'event_management_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $conn = Database::getConnection();
    $record = array();

    if (isset($_GET['id'])) {
      $query = $conn->select('events', 'm')
        ->condition('id', $_GET['id'])
        ->fields('m');
      $record = $query->execute()->fetchAssoc();
      if (!$record) {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
      }
    }

    $form['title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Title:'),
      '#required' => TRUE,
      '#default_value' => (isset($record['title'])) ? $record['title'] : '',
    );

    $form['category'] = array(
      '#type' => 'select',
      '#title' => $this->t('Category:'),
      '#required' => TRUE,
      '#options' => [
        'Category A' => 'Category A',
        'Category B' => 'Category B',
        'Category C' => 'Category C',
        'Category D' => 'Category D',
      ],
      '#default_value' => (isset($record['category'])) ? $record['category'] : '',
    );

    $form['description'] = array(
      '#type' => 'text_format',
      '#required' => TRUE,
      '#title' => $this->t('Description:'),
      '#default_value' => (isset($record['description'])) ? $record['description'] : '',
    );

    $form['image'] = array(
      '#required' => !isset($_GET['id']),
      '#type' => 'managed_file',
      '#title' => $this->t('Event image'),
      '#upload_validators' => array(
        'file_validate_extensions' => array('gif png jpg jpeg'),
        'file_validate_size' => array(25600000),
      ),
      '#upload_location' => 'public://event-images',
      '#name' => 'image'
    );

    $form['start_date'] = array(
      '#required' => TRUE,
      '#type' => 'date',
      '#title' => $this->t('Start Date:'),
      '#default_value' => (isset($record['start_date'])) ? $record['start_date'] : '',
    );
    $form['end_date'] = array(
      '#required' => TRUE,
      '#type' => 'date',
      '#title' => $this->t('End Date:'),
      '#default_value' => (isset($record['end_date'])) ? $record['end_date'] : '',
    );
    $form['is_published'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('is published'),
      '#default_value' => (isset($record['is_published'])) ? $record['is_published'] : '',
    );

    $form['submit'] = [
      '#required' => TRUE,
      '#type' => 'submit',
      '#value' => 'save',
    ];

    return $form;
  }


  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    $start_date = $form_state->getValue('start_date');
    $end_date = $form_state->getValue('end_date');
    if ($end_date < $start_date) {
      $form_state->setErrorByName('end_date', $this->t('the end date must not exceed the start date'));
    }

    parent::validateForm($form, $form_state);
  }


  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $messenger = \Drupal::service('messenger');
    $field = $form_state->getValues();

    $data  = array(
      'title'   => $field['title'],
      'category' =>  $field['category'],
      'description' =>   $field['description']['value'],
      'start_date' =>  $field['start_date'],
      'end_date' => $field['end_date'],
      'is_published' => $field['is_published'],
    );

    $fid = $form_state->getValue('image');
    if ($fid) {
      $file = \Drupal\file\Entity\File::load($fid[0]);
      $filename = $file->getFilename();
      $image = 'event-images/' . $filename;
      $data['image'] = $image;
    }

    if (isset($_GET['id'])) {
      $query = \Drupal::database();
      $query->update('events')
        ->fields($data)
        ->condition('id', $_GET['id'])
        ->execute();

      $messenger->addMessage($this->t('succesfully updated.'));
    } else {

      $query = \Drupal::database();
      $query->insert('events')
        ->fields($data)
        ->execute();

      $messenger->addMessage($this->t('succesfully saved.'));
    }

    drupal_flush_all_caches();

    $form_state->setRedirect('event_management.backend_list');
  }
}

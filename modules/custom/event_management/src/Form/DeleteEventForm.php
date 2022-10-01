<?php

namespace Drupal\event_management\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Render\Element;
use Drupal\Core\Database\Database;


/**
 * Class DeleteEventForm.
 *
 * @package Drupal\event_management\Form
 */
class DeleteEventForm extends ConfirmFormBase
{
      public $cid;


      /**
       * {@inheritdoc}
       */
      public function getFormId()
      {
            return 'delete_form';
      }

      public function getQuestion()
      {
            return $this->t('Do you want to delete %cid?', array('%cid' => $this->cid));
      }

      public function getCancelUrl()
      {
            return new Url('event_management.backend_list');
      }

      public function getDescription()
      {
            return $this->t('Only do this if you are sure!');
      }

      /**
       * {@inheritdoc}
       */
      public function getConfirmText()
      {
            return $this->t('Delete it!');
      }

      /**
       * {@inheritdoc}
       */
      public function getCancelText()
      {
            return $this->t('Cancel');
      }

      /**
       * {@inheritdoc}
       */
      public function buildForm(array $form, FormStateInterface $form_state, $cid = NULL)
      {

            $this->id = $cid;

            $query = Database::getConnection()->select('events', 'm')
                  ->condition('id', $cid)
                  ->fields('m');
            $record = $query->execute()->fetchAssoc();
            if (!$record) {
                  throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
            }

            return parent::buildForm($form, $form_state);
      }

      /**
       * {@inheritdoc}
       */
      public function validateForm(array &$form, FormStateInterface $form_state)
      {
            parent::validateForm($form, $form_state);
      }

      /**
       * {@inheritdoc}
       */
      public function submitForm(array &$form, FormStateInterface $form_state)
      {
            $query = \Drupal::database();
            $query->delete('events')
                  ->condition('id', $this->id)
                  ->execute();
            drupal_flush_all_caches();
            $messenger = \Drupal::service('messenger');
            $messenger->addMessage($this->t('succesfully deleted.'));
            $form_state->setRedirect('event_management.backend_list');
      }
}

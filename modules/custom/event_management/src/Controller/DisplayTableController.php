<?php
namespace Drupal\event_management\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;

class DisplayTableController extends ControllerBase
{

      public function display()
      {
            //create table header
            $header_table = array(
                  'id' =>    '#',
                  'title' => $this->t('Title'),

                  'edit' => '',
                  'delete' => ''
            );

            //select records from table
            $query = \Drupal::database()->select('events', 'm');
            $query->fields('m', ['id', 'title'])
                  ->orderBy('id', 'desc');
            $results = $query->execute()->fetchAll();

            $rows = array();
            foreach ($results as $data) {
                  $edit   = Url::fromUserInput('/admin/events/form?id=' . $data->id);
                  $delete = Url::fromUserInput('/admin/events/delete/' . $data->id);

                  //print the data from table
                  $rows[] = array(
                        'id' => $data->id,
                        'title' => $data->title,
                        'edit' => Link::fromTextAndUrl('Edit', $edit),
                        'delete' => Link::fromTextAndUrl('Delete', $delete),
                  );
            }

            $form['new'] = [
                  '#type' => 'link',
                  '#title' => 'Create new event',
                  '#url' =>  Url::fromUserInput('/admin/events/form'),
            ];

            $form['config'] = [
                  '#prefix' => '<br>',
                  '#type' => 'link',
                  '#title' => 'Settings',
                  '#url' =>  Url::fromUserInput('/admin/events/config'),
            ];

            //display data in site
            $form['table'] = [
                  '#type' => 'table',
                  '#header' => $header_table,
                  '#rows' => $rows,
                  '#empty' => $this->t('No events found'),
            ];
            return $form;
      }
}

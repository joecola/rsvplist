<?php

/**
 * @file  
 * Provide site admins witha list of RSVPs
 */

namespace Drupal\rsvplist\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;

class ReportController extends ControllerBase {

  /**
   * Gets and returns all RSVPs for all nodes
   * 
   * @return array/null
   */

  protected function load() {
    try{
      // api/.../introduction-to-dynamic-queries
      $database = \Drupal::database();
      $select_query = $database->select('rsvplist', 'r');

      // Join user table
      $select_query->join('users_field_data', 'u', 'r.uid = u.uid');
      // Join node table
      $select_query->join('node_field_data', 'n', 'r.nid = n.nid');

      // Select fields for output
      $select_query->addField('u', 'name', 'username');
      $select_query->addField('n', 'title');
      $select_query->addField('r', 'mail');

      // api/.../result-sets
      $entries = $select_query->execute()->fetchAll(\PDO::FETCH_ASSOC);
      return $entries;

    }
    catch (\Exception $e) {
      \Drupal::messenger()->addStatus(
        t('Databse error occurred. Please try again later.')
      );
      return NULL;
    }
  }

  /**
   * Creates an RSVP report page
   * 
   * @return array
   *  Render array for RSVPList report output
   */
  public function report() {
    $content = [];

    $content['message'] = [
      '#markup' => t('Below is a list of all RSVPs for each event.'),
    ];

    $headers = [
      t('Username'),
      t('Event'),
      t('Email'),
    ];
    $table_rows = $this->load();

    $content['table'] = [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $table_rows,
      '#empty' => t('No RSVPs found'),
    ];

    // disable cache
    $content['#cache']['max-age'] = 0;
    return $content;
  }
}

<?php

/**
 * @file
 * A form to collect an email address for RSVP details.
 */

namespace Drupal\rsvplist\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class RSVPForm extends FormBase {

/**
 * {@inheritdoc}
 */
public function getFormId() {
return 'rsvplist_email_form';
}

/**
* {@inheritDoc}
*/
public function buildForm(array $form, FormStateInterface $form_state) {
  // Attempt to get the fully loaded node object of the viewed page.
  $node = \Drupal::routeMatch()->getParameter('node');

  // Some pages may not be nodes. $node will be NULL on those pages.
  // Id a node was loaded, get the node ID.
  if ( !(is_null($node)) ) {
    $nid = $node->id();
  }
  else {
    // If a node could not be loaded, default to 0
    $nid = 0;
  }

  // Establish the $form rener array. It has an email text field,
  // a submit button and a hidden field containing the node ID.
  $form['email'] = [
    '#type' => 'textfield',
    '#title' => t('Email address'),
    '#size' => 25,
    '#description' => t("We will send updates to the email address you provide."),
    '#required' => TRUE,
  ];
  $form['submit'] = [
    '#type' => 'submit',
    '#value' => t('RSVP'),
  ];
  $form['nid'] = [
    '#type' => 'hidden',
    '#value' => $nid,
  ];

  return $form;
}

/**
 * (@inheritDoc)
 */
public function validateForm(array &$form, FormStateInterface $form_state) {
  $value = $form_state->getValue('email');
  if ( !(\Drupal::service('email.validator')->isValid($value)) ) {
    $form_state->setErrorByName('email', $this->t('Please enter a valid email address. You entered: %mail', ['%mail' => $value]));
  }
}

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
//    $submitted_email = $form_state->getValue('email');
//    $this->messenger()->addMessage(t("The form is working! Tou entered @entry.", ['@entry' => $submitted_email]));

    try {
      // p1 BEGIN Initiate variables to save.

      // Get current user ID.
      $uid = \Drupal::currentUser()->id();

      // DEMONSTRATION ONLY
      // This $full_user is not actually required for this code
      $full_user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());

      // Get form values
      $nid = $form_state->getValue('nid');
      $email = $form_state->getValue('email');

      $current_time = \Drupal::time()->getRequestTime();
      // p1 END

      // p2 BEGIN

      // Start query builder object query
      // database-api/insert-queries
      $query = \Drupal::database()->insert('rsvplist');

      // Specifiy fields to insert
      $query->fields([
        'uid',
        'nid',
        'mail',
        'created',
      ]);

      // Set values of selected fields
      // NOTE: They must be in the same order as above definition
      $query->values([
        $uid,
        $nid,
        $email,
        $current_time,
      ]);

      // Exe the query
      $query->execute();
      // p2 END

      // p3 BEGIN: display message
      \Drupal::messenger()->addMessage(
        t('Thanks for RSVPing!')
      );
      // p3 END
    }
      catch(\Exception $e) {
        \Drupal::messenger()->addError(
          t('DB error. Contact your webmonger')
        );
      }    
  } 
}

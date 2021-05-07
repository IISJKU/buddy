<?php

namespace Drupal\buddy\Form;

use Drupal\buddy\Controller\ATProviderController;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Lock\NullLockBackend;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Implements the SimpleForm form controller.
 *
 * This example demonstrates a simple form with a single text input element. We
 * extend FormBase which is the simplest form base class used in Drupal.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class ATTypeDeleteForm extends ATTypeCreateForm {
  protected $atType;

  public function buildForm(array $form, FormStateInterface $form_state,NodeInterface $type=NULL) {

    $this->atType = $type;

    $form['description'] = [
      '#type' => 'item',
      '#title' => $this->t('Do you really want to delete the following type:').$this->atType->getTitle(),
    ];

    // Group submit handlers in an actions element with a key of "actions" so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form. This is not required, but is convention.
    $form['actions'] = [
      '#type' => 'actions',
    ];

    // Add a submit button that handles the submission of the form.
    $form['actions']['delete'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Yes'),
      '#submit' => ['::deleteFormSubmit'],

    ];
    // Add a submit button that handles the submission of the form.
    $form['actions']['no_submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('No'),
    ];
    return $form;
  }



  public function getFormId() {
    return 'at_type_delete_form';
  }


  public function validateForm(array &$form, FormStateInterface $form_state) {

  }


  public function submitForm(array &$form, FormStateInterface $form_state) {


    $form_state->setRedirect('buddy.at_entry_overview');


  }

  public function deleteFormSubmit(array &$form, FormStateInterface $form_state)
  {
    $this->atType->delete();
    $form_state->setRedirect('buddy.at_entry_overview');
  }

}

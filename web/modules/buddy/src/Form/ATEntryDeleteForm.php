<?php

namespace Drupal\buddy\Form;

use Drupal\buddy\Controller\ATProviderController;
use Drupal\buddy\Util\Util;
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
class ATEntryDeleteForm extends ATEntryCreateForm {
  protected $atEntry;

  public function buildForm(array $form, FormStateInterface $form_state,NodeInterface $atEntry=NULL) {


    $this->atEntry = $atEntry;

    $form['description'] = [
      '#type' => 'item',
      '#title' => $this->t('Do you really want to delete the following entry:').$this->atEntry->getTitle(),
    ];

    // Group submit handlers in an actions element with a key of "actions" so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form. This is not required, but is convention.
    $form['actions'] = [
      '#type' => 'actions',
    ];

    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Yes'),
    ];
    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('No'),
    ];
    return $form;
  }


  public function getFormId() {
    return 'at_entry_edit_form';
  }


  public function validateForm(array &$form, FormStateInterface $form_state) {
    $title = $form_state->getValue('title');
    if (strlen($title) < 5) {
      // Set an error for the form element with a key of "title".
      $form_state->setErrorByName('title', $this->t('The title must be at least 5 characters long.'));
    }
  }


  public function submitForm(array &$form, FormStateInterface $form_state) {


    $this->atEntry->field_at_categories = $this->getSelectedCategories($form,$form_state);
    $this->atEntry->title = $form_state->getValue('title');
    $this->atEntry->save();
    $form_state->setRedirect('buddy.at_entry_overview');

  }

  public function deleteFormSubmit(array &$form, FormStateInterface $form_state)
  {
    $descriptions = $this->atEntry->get("field_at_descriptions")->getValue();
    Util::deleteNodesByReferences($descriptions);

    $types = $this->atEntry->get("field_at_types")->getValue();
    Util::deleteNodesByReferences($types);
    $this->atEntry->delete();
    $form_state->setRedirect('buddy.at_entry_overview');
  }




}

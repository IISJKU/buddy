<?php

namespace Drupal\buddy\Form;

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
class ATTypeEditForm extends ATTypeCreateForm {
  protected $atType;

  public function buildForm(array $form, FormStateInterface $form_state,NodeInterface $type=NULL) {


    $this->atType = $type;

    $platformType = $this->atType->bundle();


    if($platformType == "at_type_software"){

      $form = $this->createSoftwareTypeForm($form,$form_state,$this->atType);
    }else if($platformType== "at_type_app"){
      $form = $this->createAppTypeForm($form,$form_state,$this->atType);
    }else{
      //browser_extension
      $form = $this->createBrowserExtensionTypeForm($form,$form_state,$this->atType);

    }


    // Group submit handlers in an actions element with a key of "actions" so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form. This is not required, but is convention.
    $form['actions'] = [
      '#type' => 'actions',
    ];

    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }



  /**
   * Getter method for Form ID.
   *
   * The form ID is used in implementations of hook_form_alter() to allow other
   * modules to alter the render array built by this form controller. It must be
   * unique site wide. It normally starts with the providing module's name.
   *
   * @return string
   *   The unique ID of the form defined by this class.
   */
  public function getFormId() {
    return 'at_platform_edit_form';
  }

  /**
   * Implements form validation.
   *
   * The validateForm method is the default method called to validate input on
   * a form.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * Implements a form submit handler.
   *
   * The submitForm method is the default method called for any submit elements.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object describing the current state of the form.
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();
    foreach ($values as $fieldName => $value) {
      if (str_starts_with($fieldName, "field_")) {
        $this->atType->$fieldName = $values[$fieldName];
      }
    }

    $this->atType->save();

    $form_state->setRedirect('buddy.at_entry_overview');


  }

}

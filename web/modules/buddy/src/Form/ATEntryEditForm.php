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
class ATEntryEditForm extends ATEntryForm {
  protected $atEntry;

  public function buildForm(array $form, FormStateInterface $form_state,NodeInterface $atEntry=NULL) {


    $this->atEntry = $atEntry;

    $form = $this->createForm($form);

    $form['title']["#default_value"] = $this->atEntry->getTitle();

    $atCategories = $this->atEntry->get("field_at_categories")->getValue();

    foreach ($atCategories as $atCategory){

      foreach ($form as $categoryContainerID=>$categoryContainer){

        if(str_starts_with($categoryContainerID,"category_container_")){

          foreach ($categoryContainer as $categoryID=>$category){

            if(str_starts_with($categoryID,"category_")){

              if($atCategory["target_id"] == array_key_first ( $category["#options"])){
                $form[$categoryContainerID][$categoryID]["#default_value"] = array(array_key_first ( $category["#options"] ));
              }
            }
          }
        }
      }
    }
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
    return 'at_entry_edit_form';
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
    $title = $form_state->getValue('title');
    if (strlen($title) < 5) {
      // Set an error for the form element with a key of "title".
      $form_state->setErrorByName('title', $this->t('The title must be at least 5 characters long.'));
    }
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


    $this->atEntry->field_at_categories = $this->getSelectedCategories($form,$form_state);
    $this->atEntry->title = $form_state->getValue('title');
    $this->atEntry->save();


  }

}

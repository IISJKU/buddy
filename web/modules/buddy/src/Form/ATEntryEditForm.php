<?php

namespace Drupal\buddy\Form;

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
class ATEntryEditForm extends ATEntryCreateForm {
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

    $form['actions']['delete'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Delete'),
      '#submit' => ['::deleteFormSubmit'],

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

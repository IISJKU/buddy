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

  public function buildForm(array $form, FormStateInterface $form_state) {


    if($_GET['id']){
      $this->atEntry = Node::load($_GET['id']);


      if($this->atEntry->bundle() != "at_entry"){
        $this->atEntry = NULL;
      }


    }

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#description' => $this->t('Title must be at least 5 characters in length.'),
      '#required' => TRUE,
    ];

    $form['description'] = [
      '#type' => 'item',
      '#markup' => "<h2>".$this->t('Support categories')."</h2>",
    ];

    $storage = \Drupal::service('entity_type.manager')->getStorage('node');

    $atCategoryContainersIDs = $storage->getQuery()
      ->condition('type', 'at_category_container')
      ->condition('status', 1)
      ->sort('field_category_container_weight', 'DESC')
      ->execute();

    $atCategoryContainers = $storage->loadMultiple($atCategoryContainersIDs);

    $atCategoryIDs = $storage->getQuery()
      ->condition('type', 'at_category')
      ->condition('status', 1)
      ->sort('field_category_description', 'DESC')
      ->execute();

    $atCategories = $storage->loadMultiple($atCategoryIDs);

    foreach ($atCategoryContainers as $categoryContainerId => $atCategoryContainer){

      \Drupal::messenger()->addMessage($categoryContainerId);

      $form['category_container_'.$categoryContainerId] = array(
        '#type' => 'fieldset',
        '#title' => $this->t($atCategoryContainer->title->value),
      );

      if($atCategoryContainer->field_category_container_descrip->value){

        $form['category_container_'.$categoryContainerId]['category_container_description'] = array(
          '#type' => 'markup',
          '#markup' => $atCategoryContainer->field_category_container_descrip->value,
        );
      }


      foreach ($atCategories as $categoryID => $category){


        if($atCategoryContainer->id() == $category->get('field_at_category_container')->target_id){


          $form['category_container_'.$categoryContainerId]['category_'.$categoryID] = array(
            '#type' => 'checkboxes',
            '#title' => $category->title->value,
            '#options' => array(
              $categoryID => $category->field_category_description->value,
            ),
          );

        }
      }




    }




    /*

    $form['author'] = array(
      '#type' => 'fieldset',
      '#title' => $this
        ->t('Author'),
    );
    $form['author']['name'] = array(
      '#type' => 'textfield',
      '#title' => $this
        ->t('Name'),
    );

    $form['author']['options'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Various Options by Checkbox'),
      '#options' => array(
        'key1' => t('Option One'),
        'key2' => t('Option Two'),
        'key3' => t('Option Three'),
      ),
    );
*/
    /*
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#description' => $this->t('Title must be at least 5 characters in length.'),
      '#required' => TRUE,
    ]; */

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


  protected function createForm(array $form){


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
    return 'at_entry_form';
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
    /*
     * This would normally be replaced by code that actually does something
     * with the title.
     */

    $values = $form_state->getValues();
    $selectedCategories = array();

    foreach ($values as $key => $value){

      if(str_starts_with ($key,"category_")){

        $selectedCategories[] = [
          'target_id' => reset($value),
        ];

      }

    }

    $node = Node::create([
      'type'        => 'at_entry',
      'title'       =>  $form_state->getValue('title'),
      'field_at_categories' => $selectedCategories,
    ]);
    $node->save();
    $title = $form_state->getValue('title');
    $this->messenger()->addMessage($this->t('You specified a title of %title.', ['%title' => $title]));
  }

}

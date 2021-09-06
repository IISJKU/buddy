<?php

namespace Drupal\buddy\Form;

use Drupal\buddy\Util\Util;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\node\Entity\Node;

/**
 * Implements the SimpleForm form controller.
 *
 * This example demonstrates a simple form with a single text input element. We
 * extend FormBase which is the simplest form base class used in Drupal.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class ATEntryCreateForm extends FormBase {
  protected $atEntry;

  public function buildForm(array $form, FormStateInterface $form_state) {

    return $this->createForm($form);

  }

  protected function createForm($form){

    Util::setTitle("Create new assistive technology entry");
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#description' => $this->t('The name or your assistive technology. Must be at least 5 characters in length.'),
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
      ->sort('field_category_container_weight', 'ASC')
      ->execute();

    $atCategoryContainers = $storage->loadMultiple($atCategoryContainersIDs);

    if (empty($atCategoryContainers)) {
      $add_text = $this->t('There are no AT Containers yet. @add_link', [
        '@add_link' => Link::createFromRoute(
          $this->t('Add a new AT Container.'),
          'node.add', ['node_type' => 'at_category_container'])
          ->toString(),
      ]);
      $form['description'] = [
        '#type' => 'item',
        '#markup' => $add_text,
      ];
      return $form;
    }

    $atCategoryIDs = $storage->getQuery()
      ->condition('type', 'at_category')
      ->condition('status', 1)
      ->sort('field_category_description', 'DESC')
      ->execute();

    $atCategories = $storage->loadMultiple($atCategoryIDs);

    foreach ($atCategoryContainers as $categoryContainerId => $atCategoryContainer){

      /*
      $form['category_container_'.$categoryContainerId] = array(
        '#type' => 'fieldset',
        '#title' => $this->t($atCategoryContainer->title->value),
      );

      if($atCategoryContainer->field_category_container_descrip->value){

        $form['category_container_'.$categoryContainerId]['container_description'] = array(
          '#type' => 'markup',
          '#markup' => $atCategoryContainer->field_category_container_descrip->value,
        );
      }*/


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


  public function getFormId() {
    return 'at_entry_form';
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $title = $form_state->getValue('title');
    if (strlen($title) < 5) {
      // Set an error for the form element with a key of "title".
      $form_state->setErrorByName('title', $this->t('The title must be at least 5 characters long.'));
    }
  }


  public function submitForm(array &$form, FormStateInterface $form_state) {


    $node = Node::create([
      'type'        => 'at_entry',
      'title'       =>  $form_state->getValue('title'),
      'field_at_categories' => $this->getSelectedCategories($form,$form_state),
    ]);
    $node->setPublished(TRUE);
    $node->save();
    $form_state->setRedirect('buddy.at_entry_overview');
  }

  protected function getSelectedCategories(array &$form, FormStateInterface $form_state){
    $values = $form_state->getValues();
    $selectedCategories = array();

    foreach ($values as $key => $value){

      if(str_starts_with ($key,"category_")){

        if(reset($value) != 0){
          $selectedCategories[] = [
            'target_id' => reset($value),
          ];
        }


      }

    }

    return $selectedCategories;
  }

}

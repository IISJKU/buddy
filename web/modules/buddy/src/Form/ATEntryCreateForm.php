<?php

namespace Drupal\buddy\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\node\Entity\Node;

/**
 * AT Entry creation form
 *
 * @see \Drupal\Core\Form\FormBase
 */
class ATEntryCreateForm extends FormBase {
  protected $atEntry;

  public function buildForm(array $form, FormStateInterface $form_state) {

    return $this->createForm($form);

  }

  protected function createForm($form){

    $storage = \Drupal::service('entity_type.manager')->getStorage('node');

    $atCategoryContainersIDs = $storage->getQuery()
      ->condition('type', 'at_category_container')
      ->condition('status', 1)
      ->sort('field_category_container_weight', 'DESC')
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
    } else {
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
    }

    $atCategoryIDs = $storage->getQuery()
      ->condition('type', 'at_category')
      ->condition('status', 1)
      ->sort('field_category_description', 'DESC')
      ->execute();

    $atCategories = $storage->loadMultiple($atCategoryIDs);

    foreach ($atCategoryContainers as $categoryContainerId => $atCategoryContainer){

      $form['category_container_'.$categoryContainerId] = array(
        '#type' => 'fieldset',
        '#title' => $this->t($atCategoryContainer->title->value),
      );

      if($atCategoryContainer->field_category_container_descrip->value){

        $form['category_container_'.$categoryContainerId]['container_description'] = array(
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

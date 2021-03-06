<?php

namespace Drupal\buddy\Form;

use Drupal\buddy\Controller\ATProviderController;
use Drupal\buddy\Util\Util;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Lock\NullLockBackend;
use Drupal\Core\Url;
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

    $form['actions']['submit']['#attributes']['class'][] = 'buddy_menu_button';
    $form['actions']['back'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Cancel'),
      '#submit' => ['::backFormSubmit'],
      '#attributes' => ['class' => ['buddy_menu_button']],
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



    $this->backFormSubmit($form,$form_state);

  }

  public function backFormSubmit(array &$form, FormStateInterface $form_state)
  {

    $route_name = \Drupal::routeMatch()->getRouteName();
    if($route_name == "buddy.at_moderator_at_entry_edit_form"){
      $path = Url::fromRoute('buddy.at_moderator_at_entry_overview',
        ['atEntry' =>$this->atEntry->id()])->toString();
      $response = new \Symfony\Component\HttpFoundation\RedirectResponse($path);
      $response->send();
    }else{
      $form_state->setRedirect('buddy.at_entry_overview');
    }
  }




}

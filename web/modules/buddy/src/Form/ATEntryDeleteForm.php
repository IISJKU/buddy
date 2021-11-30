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
class ATEntryDeleteForm extends ATEntryCreateForm {
  protected $atEntry;

  public function buildForm(array $form, FormStateInterface $form_state,NodeInterface $atEntry=NULL) {


    $this->atEntry = $atEntry;

    Util::setTitle($this->t("Delete: ").$this->atEntry->getTitle());

    $form['description'] = [
      '#type' => 'item',
      '#title' => $this->t('Do you really want to delete this entry?'),
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
      '#attributes' => ['class' => ['buddy_link_button buddy_button']],

    ];
    // Add a submit button that handles the submission of the form.
    $form['actions']['no_submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('No'),
      '#attributes' => ['class' => ['buddy_link_button buddy_button']],
    ];
    return $form;
  }


  public function getFormId() {
    return 'at_entry_delete_form';
  }


  public function validateForm(array &$form, FormStateInterface $form_state) {

  }


  public function submitForm(array &$form, FormStateInterface $form_state) {

    $route_name = \Drupal::routeMatch()->getRouteName();
    if($route_name == "buddy.at_moderator_at_entry_delete_form"){

      $path = Url::fromRoute('buddy.at_moderator_at_entry_overview',
        ['atEntry' =>$this->atEntry->id()])->toString();
      $response = new \Symfony\Component\HttpFoundation\RedirectResponse($path);
      $response->send();
    }else{
      $form_state->setRedirect('buddy.at_entry_overview');
    }



  }

  public function deleteFormSubmit(array &$form, FormStateInterface $form_state)
  {


    $descriptions = $this->atEntry->get("field_at_descriptions")->getValue();
    Util::deleteNodesByReferences($descriptions);

    $types = $this->atEntry->get("field_at_types")->getValue();
    Util::deleteNodesByReferences($types);
    $this->atEntry->delete();

    $route_name = \Drupal::routeMatch()->getRouteName();
    if($route_name == "buddy.at_moderator_at_entry_delete_form"){

      $form_state->setRedirect("buddy.at_moderator_at_entry_all");
    }else{
      $form_state->setRedirect('buddy.at_entry_overview');
    }

  }




}

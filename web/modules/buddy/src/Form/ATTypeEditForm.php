<?php

namespace Drupal\buddy\Form;

use Drupal\buddy\Controller\ATProviderController;
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
      '#weight' => 999999,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',

      '#value' => $this->t('Save'),
      '#attributes' => ['class' => ['buddy_link_button buddy_button']],
    ];

    $form['actions']['back'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Cancel'),
      '#submit' => ['::backFormSubmit'],
      '#attributes' => ['class' => ['buddy_link_button buddy_button']],

    ];



    return $form;
  }



  public function getFormId() {
    return 'at_platform_edit_form';
  }


  public function validateForm(array &$form, FormStateInterface $form_state) {

  }


  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();
    foreach ($values as $fieldName => $value) {
      if (str_starts_with($fieldName, "field_")) {
        $this->atType->$fieldName = $values[$fieldName];
      }
    }

    $this->atType->save();



    $this->redirectToOverview($form_state);
  }

  public function backFormSubmit(array &$form, FormStateInterface $form_state)
  {
//    $this->atType->delete();

    $this->redirectToOverview($form_state);
  }

  private function redirectToOverview(FormStateInterface $form_state){
    $route_name = \Drupal::routeMatch()->getRouteName();
    if($route_name == "buddy.at_moderator_type_edit_form"){

      $query = \Drupal::entityQuery('node')
        ->condition('type', 'at_entry')
        ->condition('field_at_types', $this->atType->id());
      $atEntry = $query->execute();


      $atEntryID = reset($atEntry);;
      $path = Url::fromRoute('buddy.at_moderator_at_entry_overview',
        ['atEntry' =>$atEntryID])->toString();
      $response = new \Symfony\Component\HttpFoundation\RedirectResponse($path);
      $response->send();
    }else{
      $form_state->setRedirect('buddy.at_entry_overview');
    }
  }


}

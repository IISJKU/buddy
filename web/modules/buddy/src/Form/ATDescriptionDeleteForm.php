<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Controller\ATProviderController;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

class ATDescriptionDeleteForm extends ATDescriptionCreateForm
{
  protected $atDescription;

  public function getFormId()
  {
    return "at_description_delete_form";
  }

  public function buildForm(array $form, FormStateInterface $form_state, $description = null)
  {

    $this->atDescription = $description;

    $form['description'] = [
      '#type' => 'item',
      '#title' => $this->t('Do you really want to delete the following description:').$this->atDescription->getTitle(),
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

    ];
    // Add a submit button that handles the submission of the form.
    $form['actions']['no_submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('No'),
    ];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $route_name = \Drupal::routeMatch()->getRouteName();

    if($route_name == "buddy.at_moderator_description_delete_form"){
      $atEntryID = $this->atDescription->get("field_at_entry")->getValue()[0]['target_id'];
      $path = Url::fromRoute('buddy.at_moderator_at_entry_overview',
        ['atEntry' =>$atEntryID])->toString();
      $response = new \Symfony\Component\HttpFoundation\RedirectResponse($path);
      $response->send();
    }else{

      $form_state->setRedirect('buddy.at_entry_overview');
    }
  }

  public function deleteFormSubmit(array &$form, FormStateInterface $form_state)
  {


    $this->submitForm($form,$form_state);

    $atEntryID = $this->atDescription->get("field_at_entry")->getValue()[0]['target_id'];
    $atEntry = Node::load($atEntryID);
    $allDescriptions = $atEntry->get('field_at_descriptions')->getValue();
    $key = array_search($this->atDescription->id(), array_column($allDescriptions, 'target_id'));
    $atEntry->get('field_at_descriptions')->removeItem($key);
    $atEntry->save();
    $this->atDescription->delete();
  }

}

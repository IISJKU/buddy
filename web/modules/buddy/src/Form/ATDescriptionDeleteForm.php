<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Controller\ATProviderController;
use Drupal\Core\Form\FormStateInterface;

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

    $form_state->setRedirect('buddy.at_entry_overview');
  }

  public function deleteFormSubmit(array &$form, FormStateInterface $form_state)
  {
    $this->atDescription->delete();
    $form_state->setRedirect('buddy.at_entry_overview');
  }

}

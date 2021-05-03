<?php


namespace Drupal\buddy\Form;


use Drupal\Core\Form\FormStateInterface;

class ATDescriptionEditForm extends ATDescriptionCreateForm
{
  protected $atDescription;

  public function getFormId()
  {
    return "at_description_edit_form";
  }

  public function buildForm(array $form, FormStateInterface $form_state, $description = null)
  {
    $this->atDescription = $description;
    $platformType = $this->atDescription->bundle();
    return $this->createForm($form,$form_state,$this->atDescription);

  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $values = $form_state->getValues();
    foreach ($values as $fieldName => $value) {
      if (str_starts_with($fieldName, "field_")) {
        $this->atDescription->$fieldName = $values[$fieldName];
      }
    }

    $this->atDescription->save();

    $form_state->setRedirect('buddy.at_entry_overview');
  }

}

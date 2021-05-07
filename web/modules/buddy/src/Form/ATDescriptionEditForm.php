<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Controller\ATProviderController;
use Drupal\Core\Entity\RevisionLogInterface;
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

    $revision_ids = \Drupal::entityTypeManager()->getStorage('node')->revisionIds($this->atDescription);

    $last_revision_id = end($revision_ids);
    $revision = false;
    if ($this->atDescription->getRevisionId() != $last_revision_id) {
      // Load the revision.
      $this->atDescription = \Drupal::entityTypeManager()->getStorage('node')->loadRevision($last_revision_id);

      $revision = true;
    }
    $platformType = $this->atDescription->bundle();
    $form = $this->createForm($form,$form_state,$this->atDescription);

    $form['actions']['delete'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Delete'),
      '#submit' => ['::deleteFormSubmit'],

    ];

    if($revision){
      $form['actions']['submit']['#value']= $this->t('Update revision');
    }else{
      $form['actions']['submit']['#value']= $this->t('Create new revision');
    }

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $values = $form_state->getValues();
    foreach ($values as $fieldName => $value) {
      if (str_starts_with($fieldName, "field_")) {
        $this->atDescription->$fieldName = $values[$fieldName];
      }
    }
    $user = \Drupal::currentUser();
    $this->atDescription->setNewRevision(TRUE);
    $this->atDescription->revision_log = 'Created revision for node' . $nid;
    $this->atDescription->setRevisionCreationTime(REQUEST_TIME);
    $this->atDescription->setRevisionUserId($user->id());

    $this->atDescription->set('moderation_state', 'draft');
    if ($this->atDescription instanceof RevisionLogInterface) {
      $this->atDescription->setRevisionLogMessage("new version");
      $this->atDescription->setRevisionUserId($this->currentUser()->id());
    }
    $this->atDescription->save();

  //  $this->atDescription->save();

    $form_state->setRedirect('buddy.at_entry_overview');
  }

  public function deleteFormSubmit(array &$form, FormStateInterface $form_state)
  {
    $this->atDescription->delete();
    $form_state->setRedirect('buddy.at_entry_overview');
  }

}

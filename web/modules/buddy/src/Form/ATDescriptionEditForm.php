<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Controller\ATProviderController;
use Drupal\buddy\Util\Util;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

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
    $this->atDescription->setTitle($values['title']);
    foreach ($values as $fieldName => $value) {
      if (str_starts_with($fieldName, "field_")) {

        if($fieldName === "field_at_description_at_image"){

          $this->atDescription->$fieldName = [
            'target_id' => $values[$fieldName][0]['fids'][0],
            'alt' =>  $values[$fieldName][0]['alt'],
            'title' => $values[$fieldName][0]['title'],
          ];
        }else{

          if($fieldName != "field_at_entry"){
            $this->atDescription->$fieldName = $values[$fieldName];
          }
        }
      }
    }
    $user = \Drupal::currentUser();
    $this->atDescription->setNewRevision(TRUE);
    $this->atDescription->revision_log = 'Created revision for node';
    $this->atDescription->setRevisionCreationTime(REQUEST_TIME);
    $this->atDescription->setRevisionUserId($user->id());

    if(Util::hasRole("at_moderator")){
      $this->atDescription->set('moderation_state', 'published');
      $this->atDescription->setPublished(true);
    }else{
      $this->atDescription->set('moderation_state', 'draft');
      $this->messenger()->addMessage($this->t('The description has been updated and saved as draft! A moderator was informed to approve and publish the new revision!'));

    }
    if ($this->atDescription instanceof RevisionLogInterface) {
      $this->atDescription->setRevisionLogMessage("new version");
      $this->atDescription->setRevisionUserId($this->currentUser()->id());
    }



    $this->atDescription->save();

    $route_name = \Drupal::routeMatch()->getRouteName();
    if($route_name == "buddy.at_moderator_description_edit_form"){
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
    $this->atDescription->delete();
    $form_state->setRedirect('buddy.at_entry_overview');
  }

}

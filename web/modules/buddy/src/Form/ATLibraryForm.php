<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Util\Util;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class ATLibraryForm  extends FormBase
{

  public function getFormId()
  {
    return "buddy_at_library_form";
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $user = \Drupal::currentUser();

    //Get AT Entry of description
    $atRecordsIDs = \Drupal::entityQuery('node')
      ->condition('type', 'user_at_record')
      ->condition('field_user_at_record_library', true, '=')
      ->condition('uid', $user->id(), '=')
      ->execute();

    $atRecords = \Drupal::entityTypeManager()->getStorage('node')
      ->loadMultiple($atRecordsIDs);

    foreach ($atRecords as $atRecord){

      $atID = $atRecord->get("field_user_at_record_at_entry")->getValue()[0]['target_id'];


      $description = Util::getDescriptionOfAT($atID);

      $a = 1;
    }


    $form['description'] = [
      '#type' => 'markup',
      '#markup' => "<p>asdfdasfdasf</p>",
      '#allowed_tags' => ['button', 'a', 'div','img','h2','h1','p','b','b','strong','hr'],

    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    // TODO: Implement submitForm() method.
  }
}

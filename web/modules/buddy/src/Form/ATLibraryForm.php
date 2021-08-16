<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Util\Util;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

class ATLibraryForm  extends FormBase
{

  public function getFormId()
  {
    return "buddy_at_library_form";
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $user = \Drupal::currentUser();

    /*
    $markup = "<h2>".$this->t("Discover New Assistive Technology")."</h2>";
    $markup.= Link::createFromRoute($this->t('My recommendations'),'buddy.user_at_recommendation',[],  ['attributes' => ['class' => 'btn btn-primary overview-button']])->toString()->getGeneratedLink();
    $markup.= "<hr>";

    $form['recommendation'] = [
      '#type' => 'markup',
      '#markup' => $markup,
      '#allowed_tags' => ['button', 'a', 'div','img','h2','h1','p','b','b','strong','hr'],

    ];
    */

    $form['library_description'] = [
      '#type' => 'markup',
      '#markup' => '<div>'.$this->t("Here is your current library of assistive technologies").'</div>',
      '#allowed_tags' => ['button', 'a', 'div','img','h2','h1','p','b','b','strong','hr'],

    ];

    //Get AT Entry of description
    $atRecordsIDs = \Drupal::entityQuery('node')
      ->condition('type', 'user_at_record')
      ->condition('field_user_at_record_library', true, '=')
      ->condition('uid', $user->id(), '=')
      ->execute();

    $atRecords = \Drupal::entityTypeManager()->getStorage('node')
      ->loadMultiple($atRecordsIDs);

    foreach ($atRecords as $key => $atRecord){

      $atEntryID = $atRecord->get("field_user_at_record_at_entry")->getValue()[0]['target_id'];


      $description = Util::getDescriptionOfATEntry($atEntryID);

      $nid = $description->id();
      $form['description_header'.$key] = [
        '#type' => 'markup',
        '#markup' => '<h2>'.$description->getTitle().'</h2>',
        '#allowed_tags' => ['button', 'a', 'div','img','h2','h1','p','b','b','strong','hr'],

      ];

      $form['description_'.$key] = [
        '#type' => 'markup',
        '#markup' => Util::renderDescriptionTabs($description,true),
        '#allowed_tags' => ['button', 'a', 'div','img','h2','h1','p','b','b','strong','hr'],

      ];

      $form['description_actions_'.$key] = array(
        '#type' => 'fieldset',
        '#title' => $this
          ->t('Actions'),
      );
      $form['description_actions_'.$key]['install'] = [
        '#type' => 'submit',
        '#name' => $atEntryID,
        '#value' => $this->t('Installation instructions'),

      ];

      $form['description_actions_'.$key]['rate'] = [
        '#type' => 'submit',
        '#name' => $atEntryID,
        '#value' => $this->t('Rate this assistive technology'),

      ];

      $form['description_actions_'.$key]['remove'] = [
        '#type' => 'submit',
        '#name' => $atRecord->id(),
        '#value' => $this->t('Remove the assistive technology from my library'),

      ];


    }



    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    $triggeringElement = $form_state->getTriggeringElement();
    $elementID  = $form_state->getTriggeringElement()['#name'];
    $operation = $triggeringElement['#parents'][0];


    if($operation === "install"){

      $description = Util::getDescriptionOfATEntry($elementID);
      $url = Url::fromUserInput("/user-at-install/".$description->id());
      $form_state->setRedirectUrl($url);
    }else if($operation === "rate"){

    }else if($operation == "remove"){

      $userATRecord = Node::load($elementID);
      $userATRecord->field_user_at_record_library = ["value" => false];
      $userATRecord->save();
    }
  }
}

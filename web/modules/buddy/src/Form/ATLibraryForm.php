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



    //Get AT Entry of description
    $atRecordsIDs = \Drupal::entityQuery('node')
      ->condition('type', 'user_at_record')
      ->condition('field_user_at_record_library', true, '=')
      ->condition('uid', $user->id(), '=')
      ->execute();

    if(count($atRecordsIDs) == 0){

      return [
        '#type' => 'markup',
        '#markup' => '<div>'.$this->t("Your library is currently empty.").' </div><div>'
                            .$this->t("Use the search function or catalogue to add tools to your library."). '</div>',
        '#allowed_tags' => ['button', 'a', 'div','img','h2','h1','p','b','b','strong','hr'],

      ];
    }

    $form['library_description'] = [
      '#type' => 'markup',
      '#markup' => '<div>'.$this->t("Here is your current library of tools.").'</div>',
      '#allowed_tags' => ['button', 'a', 'div','img','h2','h1','p','b','b','strong','hr'],

    ];

    $atRecords = \Drupal::entityTypeManager()->getStorage('node')
      ->loadMultiple($atRecordsIDs);

    foreach ($atRecords as $key => $atRecord){

      $atEntryID = $atRecord->get("field_user_at_record_at_entry")->getValue()[0]['target_id'];


      $atEntry = Node::load($atEntryID);

      $descriptions = Util::getDescriptionsOfATEntry($atEntryID);
      $user = \Drupal::currentUser();

      $description = Util::getDescriptionForUser($descriptions,$user);
      $languages = Util::getLanguagesOfDescriptions($descriptions);
      $platforms = Util::getPlatformsOfATEntry($atEntry);
      $content = Util::renderDescriptionTiles($description,$user,$languages,$platforms,false,false);

      $form['content_'.$key] = [
        '#type' => 'markup',
        '#prefix' => "<div class='at_library_container'",
        '#markup' => $content,
        '#allowed_tags' => ['button', 'a', 'div', 'img','h3','h2', 'h1', 'p', 'b', 'b', 'strong', 'hr'],
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
        '#attributes' => ['class' => ['buddy_link_button buddy_button']],
      ];

      $form['description_actions_'.$key]['rate'] = [
        '#type' => 'submit',
        '#name' => $atEntryID,
        '#value' => $this->t('Rate the tool'),
        '#attributes' => ['class' => ['buddy_link_button buddy_button']],
      ];
      $form['at_install']['#attributes']['class'][] = 'buddy_link_button buddy_button';
      $form['description_actions_'.$key]['remove'] = [
        '#type' => 'submit',
        '#name' => $atRecord->id(),
        '#value' => $this->t('Remove the tool'),
        '#suffix' => "</div>",
        '#attributes' => ['class' => ['buddy_link_button buddy_button']],
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

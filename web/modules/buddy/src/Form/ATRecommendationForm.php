<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Util\Util;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node;

class ATRecommendationForm extends FormBase
{

  protected int $maxNumberOfATEntries = 2;

  public function getFormId()
  {
    return "buddy_recommendation_form";
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $user = \Drupal::currentUser();
    $user_lang = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'at_description')
      ->condition('field_at_description_language', $user_lang)
      ->condition('status', 1);
    $results = $query->execute();
    if (!empty($results)) {


      $atEntries = \Drupal::entityTypeManager()->getStorage('node')
        ->loadMultiple($results);

      $maxResults =  min(count($results), $this->maxNumberOfATEntries);

      for($i=0; $i < $maxResults; $i++){



        $textForm = $this->renderATEntry(array_shift($atEntries));

        $form[] = $textForm;



      }

    }



    // Group submit handlers in an actions element with a key of "actions" so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form. This is not required, but is convention.
    $form['actions'] = [
      '#type' => 'actions',
    ];

    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Show me more assitive technology!'),
    ];



    return $form;
  }


  private function renderATEntry($atDescription){



    $html = "";

    $id = $atDescription->id();

    //Get AT Entry of description
    $atEntriesID = \Drupal::entityQuery('node')
      ->condition('type', 'at_entry')
      ->condition('field_at_descriptions', $atDescription->id(), '=')
      ->execute();

    $atEntryID = intval(array_shift($atEntriesID));
    $title  = $atDescription->getTitle();

    $form = [];

    $form['title'] = [
      '#type' => 'markup',
      '#markup' => "<h2>".$title."</h2>",
      '#allowed_tags' => ['button', 'a', 'div','img','h2','h1','p','b','b','strong','hr'],

    ];

    $form['text'] = [
      '#type' => 'markup',
      '#markup' => Util::renderDescriptionTabs($atDescription,false),
      '#allowed_tags' => ['button', 'a', 'div','img','h2','h1','p','b','b','strong','hr'],
    ];


    $form['submit'] = [
      '#name' => $atEntryID."_".$id,
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('I want to try this!'),
      // Custom submission handler for page 1.
      '#submit' => ['::tryoutATSubmitHandler'],


    ];

    return $form;

  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    // TODO: Implement submitForm() method.
  }

  public function tryoutATSubmitHandler(array &$form, FormStateInterface $form_state)
  {

    $user = \Drupal::currentUser();
    $arguments = explode("_",$form_state->getTriggeringElement()['#name']);

    $atEntryID = $arguments[0];
    $descriptionID = $arguments[1];
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'user_at_record')
      ->condition('field_user_at_record_at_entry', $atEntryID)
      ->condition('uid', $user->id(), '=');

    $results = $query->execute();
    if (!empty($results)) {


      $storage = \Drupal::service('entity_type.manager')->getStorage('node');
      $entries = $storage->loadMultiple($results);
      $userATRecord = reset($entries);
      $userATRecord->field_user_at_record_library = ["value" => true];
      $userATRecord->save();


    }else{
      $node = Node::create([
        'type'        => 'user_at_record',
        'title'       => "AT Record: ".$atEntryID."-".\Drupal::currentUser()->id(),
        'field_user_at_record_at_entry' => ["target_id" => $atEntryID],
        'field_user_at_record_library' => ["value" => true],
      ]);
      $node->save();

    }

      $url = Url::fromUserInput("/user-at-install/".$descriptionID);
      $form_state->setRedirectUrl($url);

  }
}

<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Util\Util;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

class ATCatalogueForm extends FormBase
{
  protected int $maxNumberOfATEntries = 10;


  public function getFormId()
  {
    return "buddy_at_catalogue_form";
  }


  public function buildForm(array $form, FormStateInterface $form_state)
  {
    if (!$form_state->has('page_num')) {
      $form_state->set('page_num', 0);
    }
    $currentPage = $form_state->get('page_num');

    $user_lang = \Drupal::languageManager()->getCurrentLanguage()->getId();

    $query = \Drupal::entityQuery('node')
      ->condition('type', 'at_description')
      ->condition('field_at_description_language', $user_lang)
      ->condition('status', 1)
      ->range($currentPage*$this->maxNumberOfATEntries, ($currentPage+1)*$this->maxNumberOfATEntries);
    $results = $query->execute();

    $user_lang = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'at_description')
      ->condition('field_at_description_language', $user_lang)
      ->condition('status', 1);

    $count_query = $query->count()->execute();


    if (!empty($results)) {


      $atEntries = \Drupal::entityTypeManager()->getStorage('node')
        ->loadMultiple($results);

      $maxResults = min(count($results), $this->maxNumberOfATEntries);

      for ($i = 0; $i < $maxResults; $i++) {


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

    if($currentPage > 0){
      $form['actions']['previous'] = [
        '#type' => 'submit',
        '#name' => 'prev',
        '#value' => $this->t('Previous'),
      ];
    }


    if(($form_state->get('page_num')+1)*$this->maxNumberOfATEntries < $count_query)
    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#name' => 'next',
      '#value' => $this->t('Next'),
    ];


    return $form;
  }


  private function renderATEntry($atDescription)
  {


    $id = $atDescription->id();

    //Get AT Entry of description
    $atEntriesID = \Drupal::entityQuery('node')
      ->condition('type', 'at_entry')
      ->condition('field_at_descriptions', $atDescription->id(), '=')
      ->execute();

    $atEntryID = intval(array_shift($atEntriesID));
    $descriptions = Util::getDescriptionsOfATEntry($atEntryID);
    $user = \Drupal::currentUser();
    $description = Util::getDescriptionForUser($descriptions,$user);
    $languages = Util::getLanguagesOfDescriptions($descriptions);
    $platforms = Util::getPlatformsOfATEntry(Node::load($atEntryID));
    $content = Util::renderDescriptionTiles($description,$user,$languages,$platforms);

    $form = [];


    $form['content'] = [
      '#type' => 'markup',
      '#prefix' => "<div class='at_library_container'",
      '#markup' => $content,
      '#allowed_tags' => ['button', 'a', 'div', 'img','h3','h2', 'h1', 'p', 'b', 'b', 'strong', 'hr', 'ul', 'li', 'span'],
    ];


    if( \Drupal::currentUser()->isAuthenticated()){

      /*
      $form['detail'] = [
        '#name' => $atEntryID,
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => $this->t('More Information'),
        '#submit' => ['::moreInformationSubmitHandler'],
      ];
      */

      $form['at_install'] = [
        '#name' => $atEntryID . "_" . $description->id(),
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => $this->t('Try this tool'),
        '#submit' => ['::tryoutATSubmitHandler'],
        '#suffix' => '</div>'
      ];
      $form['at_install']['#attributes']['class'][] = 'buddy_link_button buddy_button';
    }else{
      $form['at_install'] = [
        '#type' => 'markup',
        '#markup' => '</div>',
        '#allowed_tags' => ['button', 'a', 'div', 'img', 'h2', 'h1', 'p', 'b', 'b', 'strong', 'hr'],
      ];
    }

    return $form;

  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    if($form_state->getTriggeringElement()['#name'] === "prev"){
      $form_state->set('page_num', $form_state->get('page_num')-1);
    }else  if($form_state->getTriggeringElement()['#name'] === "next"){
      $form_state->set('page_num', $form_state->get('page_num')+1);
    }

    $form_state->setRebuild(true);
  }

  public function moreInformationSubmitHandler(array &$form, FormStateInterface $form_state)
  {

    $url = Url::fromUserInput("/user-at-detail/" . $form_state->getTriggeringElement()['#name']);
    $form_state->setRedirectUrl($url);

  }

  public function tryoutATSubmitHandler(array &$form, FormStateInterface $form_state)
  {

    $user = \Drupal::currentUser();
    $arguments = explode("_", $form_state->getTriggeringElement()['#name']);

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


    } else {
      $node = Node::create([
        'type' => 'user_at_record',
        'title' => "AT Record: " . $atEntryID . "-" . \Drupal::currentUser()->id(),
        'field_user_at_record_at_entry' => ["target_id" => $atEntryID],
        'field_user_at_record_library' => ["value" => true],
      ]);
      $node->save();

    }

    $url = Url::fromUserInput("/user-at-install/" . $descriptionID);
    $form_state->setRedirectUrl($url);

  }
}

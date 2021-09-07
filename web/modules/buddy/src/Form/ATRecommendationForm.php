<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Util\BuddyRecommender;
use Drupal\buddy\Util\Util;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

class ATRecommendationForm extends FormBase
{

  public function getFormId()
  {
    return "buddy_recommendation_form";
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    if (!$form_state->has('page_num')) {
      $form_state->set('page_num', 0);
    }
    $user = \Drupal::currentUser();

    $recommendations_all = array();
    $storage = $form_state->getStorage();
    if (array_key_exists('recommendations', $storage)) {
      $recommendations_all = $storage['recommendations'];
    }

    $recommendations = BuddyRecommender::recommend($user, $recommendations_all);

    if (!empty($recommendations)) {
      $maxResults = min(count($recommendations), BuddyRecommender::$maxNumberOfATEntries);
      for ($i = 0; $i < $maxResults; $i++) {
        $at_entry = array_shift($recommendations);
        $atDesc = Util::getDescriptionOfATEntry($at_entry);
        $textForm = $this->renderATEntry($atDesc);
        $form[] = $textForm;
        $recommendations_all[] = $at_entry;
      }
      $form['actions'] = [
        '#type' => 'actions',
      ];

      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Show me more assistive technology!'),
      ];
    } else {
      $form['title'] = [
        '#type' => 'markup',
        '#markup' => "<div class='at_container'><h2>Nothing to show</h2>",
        '#allowed_tags' => ['div','h2'],
      ];

      $form['text'] = [
        '#type' => 'markup',
        '#markup' => '<p>'. t("There are no more ATs to recommend at the moment!") .'</p>',
        '#allowed_tags' => ['button', 'a', 'div', 'img', 'h2', 'h1', 'p', 'b', 'b', 'strong', 'hr'],
      ];
    }

    // Store recommendations
    $form_state->setStorage(array('recommendations' => $recommendations_all));

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
    $title = $atDescription->getTitle();

    $form = [];

    $form['title'] = [
      '#type' => 'markup',
      '#markup' => "<div class='at_container'><h2>" . $title . "</h2>",
      '#allowed_tags' => ['button', 'a', 'div', 'img', 'h2', 'h1', 'p', 'b', 'b', 'strong', 'hr'],

    ];

    $form['text'] = [
      '#type' => 'markup',
      '#markup' => Util::renderDescriptionTabs($atDescription, false),
      '#allowed_tags' => ['button', 'a', 'div', 'img', 'h2', 'h1', 'p', 'b', 'b', 'strong', 'hr'],
    ];

    $form['submit'] = [
      '#name' => $atEntryID . "_" . $id,
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('I want to try this!'),
      // Custom submission handler for page 1.
      '#submit' => ['::tryoutATSubmitHandler'],
      '#suffix' => '</div>'
    ];

    return $form;

  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $recommendations = $form_state->getStorage()['recommendations'];
    if (($form_state->get('page_num')+1)*BuddyRecommender::$maxNumberOfATEntries >= count($recommendations)) {
      $form_state->set('page_num', 0);
    } else {
      $form_state->set('page_num', $form_state->get('page_num')+1);
    }
    $form_state->setRebuild(true);
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

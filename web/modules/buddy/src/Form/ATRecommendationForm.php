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



    $ajaxUpdate =  $form_state->get('ajax_update');
    if($ajaxUpdate){
      $recommendations = $form_state->get('recommendations');
      $form_state->set('ajax_update',false);
    }else{
      $recommendations_all = array();
      $storage = $form_state->getStorage();
      if (array_key_exists('recommendations', $storage)) {
        $recommendations_all = $storage['recommendations'];
      }
      $recommendations = BuddyRecommender::recommend($user, $recommendations_all);
      $form_state->set('recommendations',$recommendations);
    }


    if (!empty($recommendations)) {
      $maxResults = min(count($recommendations), BuddyRecommender::$maxNumberOfATEntries);
      for ($i = 0; $i < $maxResults; $i++) {
        $atEntryID = array_shift($recommendations);

        $descriptions = Util::getDescriptionsOfATEntry($atEntryID);
        $user = \Drupal::currentUser();
        $supportCategories = Util::getSupportCategoriesOfAtEntry(Node::load($atEntryID));
        $description = Util::getDescriptionForUser($descriptions,$user);
        $languages = Util::getLanguagesOfDescriptions($descriptions);
        $platforms = Util::getPlatformsOfATEntry(Node::load($atEntryID));
        $content = Util::renderDescriptionTiles($description,$user,$languages,$platforms,$supportCategories);

        $content = Util::renderDescriptionTiles2($description,$supportCategories,$platforms,$languages,false,2);

        $entryForm = [];
        /*
        $entryForm['content'] = [
          '#type' => 'markup',
          '#prefix' => "<div class='at_library_container'",
          '#markup' => $content,
          '#allowed_tags' => ['button', 'a', 'div', 'img','h3','h2', 'h1', 'p', 'b', 'b', 'strong', 'hr', 'ul', 'li', 'span'],
        ];*/
        /*
        $entryForm['at_install'] = [
          '#name' => $atEntryID . "_" . $description->id(),
          '#type' => 'submit',
          '#button_type' => 'primary',
          '#value' => $this->t('Try this tool'),
          '#submit' => ['::tryoutATSubmitHandler'],
          '#prefix' => '<div class="at_library_container">'.$content.'<div class="col-2">',
          '#suffix' => '</div></div></div></div>'
        ];
        */
        $entryForm['content'] = [
          '#type' => 'markup',
          '#prefix' => "<div class='at_library_container'",
          '#suffix' => '<div class="col-2">',
          '#markup' => $content,
          '#allowed_tags' => ['button', 'a', 'div', 'img','h3','h2', 'h1', 'p', 'b', 'b', 'strong', 'hr', 'ul', 'li', 'span'],
        ];

        $atRecord = Util::getATRecordOfATEntry($atEntryID);
        $valueLabel = $this->getLabelForFavourite(false);
        if($atRecord){
          $valueLabel = $this->getLabelForFavourite(true);
        }
        $entryForm['at_favourites'] = [
          '#name' => $atEntryID . "_" . $description->id(),
          '#type' => 'submit',
          '#button_type' => 'primary',
          '#value' =>$valueLabel,
          '#submit' => ['::test1'],
          '#ajax' => array(
            'callback' => '::test2',
            'wrapper' => "favourites_wrapper_".$atEntryID,
          ),
          '#prefix' => '<div id="favourites_wrapper_'.$atEntryID.'">',
          '#suffix' => '</div>'
        ];

        $entryForm['close'] = [
          '#type' => 'markup',
           '#markup' => '</div></div></div></div>',
          '#allowed_tags' => ['button', 'a', 'div', 'img','h3','h2', 'h1', 'p', 'b', 'b', 'strong', 'hr', 'ul', 'li', 'span'],
        ];
        $entryForm['at_favourites']['#attributes']['class'][] = 'buddy_link_button buddy_button';

        $form[$atEntryID] = $entryForm;

        $recommendations_all[] = $atEntryID;
      }
      $form['actions'] = [
        '#type' => 'actions',
      ];

      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Show me more tools!'),
      ];
      $form['actions']['submit']['#attributes']['class'][] = 'buddy_link_button buddy_button';
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

  public function test1(array &$form, FormStateInterface $form_state) {

    $user = \Drupal::currentUser();
    $test = $form_state->getTriggeringElement()['#name'];
    $arguments = explode("_", $form_state->getTriggeringElement()['#name']);

    $atEntryID = $arguments[0];

    $atRecord = Util::getATRecordOfATEntry($atEntryID);
    $isFavourite = false;
    if($atRecord){

      $atRecord->delete();

    }else{
      $node = Node::create([
        'type' => 'user_at_record',
        'title' => "AT Record: " . $atEntryID . "-" . \Drupal::currentUser()->id(),
        'field_user_at_record_at_entry' => ["target_id" => $atEntryID],
        'field_user_at_record_library' => ["value" => true],
      ]);
      $node->save();


    }

    $form_state->set('ajax_update', true);
    $form_state->setRebuild();


  }
  public function test2(array &$form, FormStateInterface $form_state) {
    $arguments = explode("_", $form_state->getTriggeringElement()['#name']);
    $atEntryID = $arguments[0];
    return $form[$atEntryID]['at_favourites'];
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

  private function getLabelForFavourite($isFavourite){
    if($isFavourite){
      return $this->t("Remove from favourites");
    }

    return $this->t("Add to favourites");
  }
}

<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Util\Util;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node;

class UserATEntryDetailForm extends FormBase
{

  public function getFormId()
  {
    return "buddy_user_at_entry_detail_form";
  }

  public function buildForm(array $form, FormStateInterface $form_state, $description = null)
  {


    $id = $description->id();

    //Get AT Entry of description
    $atEntriesID = \Drupal::entityQuery('node')
      ->condition('type', 'at_entry')
      ->condition('field_at_descriptions', $description->id(), '=')
      ->execute();

    $atEntryID = intval(array_shift($atEntriesID));
    $title = $description->getTitle();
    Util::setTitle($title);
    $form = [];

    $image = $description->field_at_description_at_image->getValue();
    $altText = $image[0]['alt'];
    $styled_image_url = ImageStyle::load('medium')->buildUrl($description->field_at_description_at_image->entity->getFileUri());

    $form['title'] = [
      '#type' => 'markup',
      '#markup' => "<div class='image_container'><img src='".$styled_image_url."' alt='".$altText."' class='buddy-type-icon'></div>",
      '#allowed_tags' => ['button', 'a', 'div', 'img', 'h2', 'h1', 'p', 'b', 'b', 'strong', 'hr'],

    ];

    $form['text'] = [
      '#type' => 'markup',
      '#markup' => Util::renderDescriptionTabs($description, false),
      '#allowed_tags' => ['button', 'a', 'div', 'img', 'h2', 'h1', 'p', 'b', 'b', 'strong', 'hr'],
    ];

    $form['try'] = [
      '#name' => $atEntryID . "_" . $id,
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

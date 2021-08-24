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
    $title = $atDescription->getTitle();

    $form = [];

    $form['title'] = [
      '#type' => 'markup',
      '#markup' => "<div class='at_container'><h2>" . $title . "</h2>",
      '#allowed_tags' => ['button', 'a', 'div', 'img', 'h2', 'h1', 'p', 'b', 'b', 'strong', 'hr'],

    ];

    $form['text'] = [
      '#type' => 'markup',
      '#markup' => Util::renderDescriptionTabs($atDescription, true),
      '#allowed_tags' => ['button', 'a', 'div', 'img', 'h2', 'h1', 'p', 'b', 'b', 'strong', 'hr'],
    ];

    if (\Drupal::currentUser()->isAuthenticated()) {
      $closeDiv = true;
      $form['detail'] = [
        '#name' => $id,
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => $this->t('More Information'),
        '#submit' => ['::moreInformationSubmitHandler'],
      ];
      $form['submit'] = [
        '#name' => $atEntryID . "_" . $id,
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => $this->t('Install this AT'),
        '#submit' => ['::tryoutATSubmitHandler'],
      ];

      $query = \Drupal::entityQuery('rate_widget');
      $query->condition('entity_types.*', ['node.at_entry'], 'IN');
      $widget_ids = $query->execute();

      if (isset($widget_ids) && count($widget_ids) > 0) {
        $widget_storage = \Drupal::service('entity_type.manager')->getStorage('rate_widget');
        $rate_widget_base_service = \Drupal::service('rate.vote_widget_base');

        $widget_name = array_shift($widget_ids);
        $widget = $widget_storage->load($widget_name);
        $widget_template = $widget->get('template');
        $value_type = $widget->get('value_type');

        $vote_type = ($widget_template == 'fivestar') ? $widget_template : 'updown';
        $rate_form = $rate_widget_base_service->getForm('node', 'at_entry', $id, $vote_type, $value_type, $widget_name, $widget);
        $rate_form_container = [
          'rating' => [
            '#theme' => 'container',
            '#attributes' => [
              'class' => ['rate-widget', $widget_template],
            ],
            '#children' => [
              'form' => $rate_form,
            ],
          ],
          '#attached' => [
            'library' => ['rate/w-' . $widget_template],
          ],
        ];
        $form['rate'] = $rate_form_container;
      }
      if ($closeDiv) {
        $form['submit'] = [
          '#type' => 'markup',
          '#markup' => '</div>',
        ];
      }
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

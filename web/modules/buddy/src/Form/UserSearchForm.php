<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Util\Util;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

class UserSearchForm extends FormBase
{

  public function getFormId()
  {
    return "user_search_form";
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {


    $storage = \Drupal::service('entity_type.manager')->getStorage('node');





    $form['search'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search for assistive technology'),
      '#required' => TRUE,
    ];
    $form['advanced'] = array(
      '#type' => 'details',
      '#title' => t('Advanced settings'),
   //   '#description' => t('Lorem ipsum.'),
      '#open' => FALSE, // Controls the HTML5 'open' attribute. Defaults to FALSE.
    );
    $form['advanced']['language'] = array(
      '#type' => 'radios',
      '#title' => t('Supported languages'),
      '#options' => array(
        'own_language' => t('Search only for my language'),
        'all_languages' => t('Search in all languages'),
      ),
      '#default_value' => 'own_language',
    );

    $form['actions']['search'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#attributes' => ['class' => ['buddy_menu_button','buddy_menu_button_large','buddy_mobile_100']],
    ];

    $categoryIDs = $storage->getQuery()
      ->condition('type', 'at_category')
      ->condition('status', 1)
      ->sort('field_at_category_user_title', 'ASC')
      ->execute();

    $atCategories = $storage->loadMultiple($categoryIDs);

    $categoryOptions = [];
    foreach ($atCategories as $categoryContainerId => $atCategory){
      $userTitle = $atCategory->get("field_at_category_user_title")->getValue()[0]['value'];
      $categoryOptions[$categoryContainerId] = $this->t($userTitle) ;

    }

    $form['advanced']['categories'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Supported languages'),
      '#options' => $categoryOptions,
    );

    $searchResults = $form_state->get('search_results');
    if(is_array($searchResults)){

      if(!count($searchResults)){
        $form['search_results'] = [
          '#type' => 'markup',
          '#prefix' => "<hr><div class='at_search_results'>",
          '#markup' => "<h1>".t('Search results')."</h1>".t('Your search yielded no results.'),
          '#allowed_tags' => ['button', 'a', 'div', 'img','h3','h2', 'h1', 'p', 'b', 'b', 'strong', 'hr'],
          '#suffix' => "</div>",
        ];

      }else{
        $form['search_results'] = [
          '#type' => 'markup',
          '#prefix' => "<hr><div class='at_search_results'>",
          '#markup' => "<h1>".t('Search results')."</h1>",
          '#allowed_tags' => ['button', 'a', 'div', 'img','h3','h2', 'h1', 'p', 'b', 'b', 'strong', 'hr'],
          '#suffix' => "</div>",
        ];
        foreach ($searchResults as $searchResult){

          $textForm = $this->renderATEntry($searchResult);
          if($textForm){

            $form['search_results'][$searchResult] = $textForm;
          }
        }


      }



    }
    return $form;

  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    $values = $form_state->getValues();


    //INDEX WHOLE Fields:
    //http://localhost/buddy/web/admin/config/search/search-api/index/default_index/processors
    //Go bottom and enable html filter, ignore case , stopwords, etc on all supported fields(fist checkbox in each tab)

    $index = \Drupal\search_api\Entity\Index::load('default_index');
    $query = $index->query();

// Change the parse mode for the search.

    $parse_mode = \Drupal::service('plugin.manager.search_api.parse_mode')
      ->createInstance('direct');
    $parse_mode->setConjunction('OR');
    $query->setParseMode($parse_mode);

// Set fulltext search keywords and fields.
    $query->keys($values['search']);
    $query->setFulltextFields(['title', 'name', 'field_at_description']);

    if($values['language'] == "own_language"){
      $user_lang = \Drupal::languageManager()->getCurrentLanguage()->getId();
      $query->addCondition('field_at_description_language', $user_lang,"=");
    }

    $categoryFilter = false;
    $conditions = $query->createConditionGroup('OR');
    foreach ($values['categories'] as $category){
      if($category){
        $conditions->addCondition('field_at_categories',$category);
        $categoryFilter = true;
      }
    }

    if($categoryFilter){
      $query->addConditionGroup($conditions);
    }




   //$query->addCondition('field_at_categories',89);
// Set additional conditions.
    /*
    $query->addCondition('status', 1)
      ->addCondition('author', 1, '<>');

// Add more complex conditions.
// (In this case, a condition for a specific datasource).
    $time = \Drupal::service('datetime.time')->getRequestTime();
    $conditions = $query->createConditionGroup('OR');
    $conditions->addCondition('search_api_datasource', 'entity:node', '<>')
      ->addCondition('created', $time - 7 * 24 * 3600, '>=');
    $query->addConditionGroup($conditions);
*/
// Restrict the search to specific languages.
    //  $query->setLanguages(['de', 'it']);

// Do paging.

    // $query->range(0, 10);

// Add sorting.
    $query->sort('search_api_relevance', 'DESC');

// Set additional options.
// (In this case, retrieve facets, if supported by the backend.)
    /*
    $server = $index->getServerInstance();
    if ($server->supportsFeature('search_api_facets')) {
      $query->setOption('search_api_facets', [
        'type' => [
          'field' => 'type',
          'limit' => 20,
          'operator' => 'AND',
          'min_count' => 1,
          'missing' => TRUE,
        ],
      ]);
    }
    */

// Set one or more tags for the query.
// @see hook_search_api_query_TAG_alter()
// @see hook_search_api_results_TAG_alter()
    $query->addTag('custom_search');

// Execute the search.
    $results = $query->execute();
    $items = $results->getResultItems();
    $resultIDs = [];
    foreach ($items as $item){
      $idString = $item->getId();
      $resultIDs[] = explode(":", explode("/", $idString)[1])[0];

    }

    $form_state->set('search_results', $resultIDs);
    $ids = implode(', ', array_keys($results->getResultItems()));

    $form_state->setRebuild(TRUE);
  }



  private function renderATEntry($atEntryID)
  {
    $atEntry = Node::load($atEntryID);

    $descriptions = Util::getDescriptionsOfATEntry($atEntryID);
    if(count($descriptions) == 0){
      return;
    }
    $user = \Drupal::currentUser();

    $description = Util::getDescriptionForUser($descriptions,$user);
    $languages = Util::getLanguagesOfDescriptions($descriptions);
    $platforms = Util::getPlatformsOfATEntry($atEntry);
    if(count($platforms) == 0){
      return;
    }
    $supportCategories = Util::getSupportCategoriesOfAtEntry(Node::load($atEntryID));

    $form = [];

    if( \Drupal::currentUser()->isAuthenticated()){
      $content = Util::renderDescriptionTiles2($description,$supportCategories,$platforms,$languages,false,3);

      $form['content'] = [
        '#type' => 'markup',
        '#prefix' => "<div class='at_library_container'>",
        '#suffix' => '<div class="col-12 col-lg-3 buddy_favourite_col buddy_recommendation_menu">',
        '#markup' => $content,
        '#allowed_tags' => ['button', 'a', 'div', 'img','h3','h2', 'h1', 'p', 'b', 'b', 'strong', 'hr', 'ul', 'li', 'span'],
      ];

      $atRecord = Util::getATRecordOfATEntry($atEntryID);
      $valueLabel = $this->getLabelForFavourite($atRecord);

      $form['at_favourites'] = [
        '#name' => $atEntryID . "_" . $description->id(),
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' =>$valueLabel,
        '#submit' => ['::favouriteSubmit'],
        '#ajax' => array(
          'callback' => '::favouriteAjaxCallback',
          'wrapper' => "favourites_wrapper_".$atEntryID,
        ),
        '#prefix' => '<div id="favourites_wrapper_'.$atEntryID.'">',
        '#suffix' => '</div></div></div>',
        '#attributes' => [
        'class' => ['buddy-icon-button', 'buddy-icon-after','buddy_menu_button','buddy_invert_button','buddy_favourites_button'],
        'icon' => "fa-plus",
         ]
      ];
      if($this->isFavourite($atRecord)){
        $form['at_favourites']['#attributes']['icon'] = "fa-minus";
      }

      $installLink = Link::createFromRoute($this->t('How to get this tool'),'buddy.user_at_install_form',['description' => $description->id(),"return"=>"search"],  ['attributes' => ['class' => 'buddy_menu_button']])->toString()->getGeneratedLink();
      $installHtml = ' <div class="row">
    <div class="col">
    </div>
    <div class="col buddy_recommendation_menu text-align-right">
        '.$installLink.'
    </div>
    </div>';

      $form['install'] = [
        '#type' => 'markup',
        '#markup' => $installHtml.'</div></div>',
        '#allowed_tags' => ['button', 'a', 'div', 'img','h3','h2', 'h1', 'p', 'b', 'b', 'strong', 'hr', 'ul', 'li', 'span'],
      ];

    }else{

      $content = Util::renderDescriptionTiles2($description,$supportCategories,$platforms,$languages);
      $form['content'] = [
        '#type' => 'markup',
        '#prefix' => "<div class='at_library_container'",
        '#markup' => $content,
        '#allowed_tags' => ['button', 'a', 'div', 'img','h3','h2', 'h1', 'p', 'b', 'b', 'strong', 'hr', 'ul', 'li', 'span'],
      ];

      $installLink = Link::createFromRoute($this->t('How to get this tool'),'buddy.user_at_install_form',['description' => $description->id(),"return"=>"search"],  ['attributes' => ['class' => 'buddy_menu_button']])->toString()->getGeneratedLink();
      $installHtml = ' <div class="row">
    <div class="col">
    </div>
    <div class="col buddy_recommendation_menu text-align-right">
        '.$installLink.'
    </div>
    </div>';

      $form['install'] = [
        '#type' => 'markup',
        '#markup' => $installHtml,
        '#allowed_tags' => ['button', 'a', 'div', 'img','h3','h2', 'h1', 'p', 'b', 'b', 'strong', 'hr', 'ul', 'li', 'span'],
        '#suffix' => '</div>'
      ];

    }




    return $form;

  }


  public function moreInformationSubmitHandler(array &$form, FormStateInterface $form_state)
  {

    $url = Url::fromUserInput("/user-at-detail/" . $form_state->getTriggeringElement()['#name']);
    $form_state->setRedirectUrl($url);

  }

  public function installATSubmitHandler(array &$form, FormStateInterface $form_state)
  {
    Util::installATSubmitHandler($form,$form_state);
  }

  private function getLabelForFavourite($atRecord){
    if($atRecord){

      $isLibrary = $atRecord->field_user_at_record_library->getValue()[0]['value'];

      if($isLibrary){
        return $this->t("Remove from favourites");
      }

    }

    return $this->t("Add to favourites");
  }

  public function favouriteAjaxCallback(array &$form, FormStateInterface $form_state) {
    $arguments = explode("_", $form_state->getTriggeringElement()['#name']);
    $atEntryID = $arguments[0];
    return $form['search_results'][$atEntryID]['at_favourites'];
  }

  public function favouriteSubmit(array &$form, FormStateInterface $form_state) {

    $arguments = explode("_", $form_state->getTriggeringElement()['#name']);

    $atEntryID = $arguments[0];

    $atRecord = Util::getATRecordOfATEntry($atEntryID);
    if($atRecord){

      $isLibrary = $atRecord->field_user_at_record_library->getValue()[0]['value'];

      if($isLibrary){
        $atRecord->set("field_user_at_record_library", false);
      }else{
        $atRecord->set("field_user_at_record_library", true);
      }

      $atRecord->save();

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

  private function isFavourite($atRecord){
    if($atRecord){

      $isLibrary = $atRecord->field_user_at_record_library->getValue()[0]['value'];

      if($isLibrary){
        return true;
      }

    }

    return false;
  }
}

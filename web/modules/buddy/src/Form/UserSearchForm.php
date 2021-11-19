<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Util\Util;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
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
      '#title' => $this->t('Search'),
      '#description' => $this->t('Search for asssistive technology.'),
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
    ];
    $form['actions']['search']['#attributes']['class'][] = 'buddy_link_button buddy_button';

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
          $form['search_results'][] = $textForm;
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
      $query->addCondition('field_at_description_language', "de","=");
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
    $user = \Drupal::currentUser();

    $description = Util::getDescriptionForUser($descriptions,$user);
    $languages = Util::getLanguagesOfDescriptions($descriptions);
    $platforms = Util::getPlatformsOfATEntry($atEntry);
    $content = Util::renderDescriptionTiles($description,$user,$languages,$platforms);

    $form = [];


    $form['content'] = [
      '#type' => 'markup',
      '#prefix' => "<div class='at_library_container'",
      '#markup' => $content,
      '#allowed_tags' => ['button', 'a', 'div', 'img','h3','h2', 'h1', 'p', 'b', 'b', 'strong', 'hr'],
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
        '#submit' => ['::installATSubmitHandler'],
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


  public function moreInformationSubmitHandler(array &$form, FormStateInterface $form_state)
  {

    $url = Url::fromUserInput("/user-at-detail/" . $form_state->getTriggeringElement()['#name']);
    $form_state->setRedirectUrl($url);

  }

  public function installATSubmitHandler(array &$form, FormStateInterface $form_state)
  {
    Util::installATSubmitHandler($form,$form_state);
  }

}
<?php


namespace Drupal\buddy\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class UserSearchForm extends FormBase
{

  public function getFormId()
  {
    return "user_search_form";
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {

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
    $query->keys('Änderungen');
    $query->setFulltextFields(['title', 'name', 'field_at_description']);
    $query->addCondition('field_at_description_language', "de","=");
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
    $ids = implode(', ', array_keys($results->getResultItems()));
    $a = 1;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    // TODO: Implement submitForm() method.
  }
}

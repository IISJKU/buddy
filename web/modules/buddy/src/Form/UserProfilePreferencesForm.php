<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Util\Util;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

class UserProfilePreferencesForm extends FormBase
{


  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'buddy_user_profile_preferences_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {




    $storage = \Drupal::service('entity_type.manager')->getStorage('node');

    $atCategoryContainersIDs = $storage->getQuery()
      ->condition('type', 'at_category_container')
      ->condition('status', 1)
      ->sort('field_category_container_weight', 'ASC')
      ->execute();

    $atCategoryContainers = $storage->loadMultiple($atCategoryContainersIDs);

    $atCategoryIDs = $storage->getQuery()
      ->condition('type', 'at_category')
      ->condition('status', 1)
      ->sort('field_category_weight', 'ASC')
      ->execute();

    $atCategories = $storage->loadMultiple($atCategoryIDs);

    /*
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

    foreach ($atCategories as $atCategory) {
      if($atCategory->hasTranslation($language)) {
        $atCategory = $atCategory->getTranslation($language);

        $this->messenger()->addMessage($language);

      }
    }
    */

    $form_state->set('category_count', count($atCategories));
    if (!$form_state->has('category_container_num')) {


      $selectedAtCategories = [];

      foreach ($atCategories as $key=> $atCategory){

        $selectedAtCategories[$key] = 0;
        $form_state->set('selectedAtCategories', $selectedAtCategories);
      }




      $form_state->set('category_container_num', 0);
      $form_state->set('category_num', 0);
      $form_state->set('progress', 0);

      $user = \Drupal::currentUser();
      $user_profileID = \Drupal::entityQuery('node')
        ->condition('type', 'user_profile')
        ->condition('uid', $user->id(), '=')
        ->execute();


      if(count($user_profileID) == 0){
        $node = Node::create([
          'type'        => 'user_profile',
          'title'       =>  'User Profile:'.$user->id(),

        ]);
        $node->save();

        $form_state->set('user_profile_id',$node->id());

      }else{

        $form_state->set('user_profile_id', reset($user_profileID));

        $existingUserNeedAssignmentIDs = \Drupal::entityQuery('node')
          ->condition('type', 'user_need_assignment')
          ->condition('field_user_need_ass_user_profile',reset($user_profileID), '=')
          ->execute();


        $existingUserNeedAssignments = $storage->loadMultiple($existingUserNeedAssignmentIDs);

        foreach ($existingUserNeedAssignments as $existingUserNeedAssignment) {

          $selectedAtCategories[$existingUserNeedAssignment->field_user_need_ass_support_cat->getValue()[0]['target_id']] = $existingUserNeedAssignment->field_user_need_ass_percentage->getValue()[0]['value'];

        }
        $form_state->set('selectedAtCategories', $selectedAtCategories);
      }




      /*

      $node->save();
      */


    }

    $selectedAtCategories = $form_state->get('selectedAtCategories');
    $currentPage = $form_state->get('category_container_num');
    $currentCategoryNumber = $form_state->get('category_num');
    $keys = array_keys($atCategoryContainers);
    $categoryContainerId = $keys[$currentPage];
    $atCategoryContainer = $atCategoryContainers[$categoryContainerId];

    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    if($atCategoryContainer->hasTranslation($language)) {
      $atCategoryContainer = $atCategoryContainer->getTranslation($language);


    }

    $sortedCategories = [];
    $index = 0;
    foreach ($atCategoryContainers as $categoryContainer){

      $sortedCategories[$index] = ['id'=>$categoryContainer->id(),
                              'categories' => []];
      $index++;
    }

    $index = 0;
    $currentCategory = null;
    foreach ($atCategories as $categoryID => $category) {
      $categoryIndex = 0;
      foreach ($sortedCategories as $key => $sortedCategory){
        if($sortedCategory['id'] == $category->get('field_at_category_container')->target_id){
          $categoryIndex = $key;
        }
      }
      $sortedCategories[$categoryIndex]['categories'][] = [
        'id' => $category->id(),
        'skipped' => false,
      ];

      if ($atCategoryContainer->id() == $category->get('field_at_category_container')->target_id) {


        if($index == $currentCategoryNumber){
          $currentCategory = $category;

          $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
          if($category->hasTranslation($language)) {
            $currentCategory = $category->getTranslation($language);


          }
        }
        $index++;

      }
    }

    $form_state->set('categories', $sortedCategories);



    if ($atCategoryContainer->field_category_container_user_ti->value) {

      $title = $this->t("Set your preferences")."";
   //   $title = $this->t("Create Profile:")." ".$atCategoryContainer->field_category_container_user_ti->value;
      Util::setTitle($title);
      $form_state->set('current_title',$title);

    }else{
      $title = $this->t("Set your preferences")."";
      //$title = $this->t("Create Profile:")." ".$atCategoryContainer->title->value;
      Util::setTitle($title);
      $form_state->set('current_title',$title);
    }




    $form['progress']  = array(
      '#type' => 'markup',
      '#markup' => '
  <div class="row">
    <div class="col-12 col-md-4 text-align-right profile-progress">
      <b id="progress_label">'.$this->t("Progress").':</b>
    </div>
    <div class="col-12 col-md-5">
      <div class="progress">
        <div id="profile-progress-bar" aria-labelledby="progress_label" class="progress-bar progress-bar-striped" role="progressbar" style="width: 10%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
        <span class="sr-only">0% complete</span>
      </div>
    </div>
</div>',
    );


    $prefixHTML = '<div class="buddy-user-profile-category-container"><div class="row">
  <div class="col-12 col-lg-2"></div>
  <div class="col-12 col-lg-3 buddy-category-image">'.$currentCategory->field_at_category_user_descript->value.'</div>
  <div class="col-12 col-lg-5"><h2>'.$currentCategory->field_at_category_user_title->value.'</h2>';
    if ($atCategoryContainer->field_category_container_user_de->value) {

      $form['category_container_' . $categoryContainerId]['container_description'] = array(
        '#type' => 'markup',
        '#markup' => $atCategoryContainer->field_category_container_user_de->value,
        '#allowed_tags' => ['style'],
      );
    }




/*
    $form['category_container_' . $categoryContainerId]['category_' . $currentCategory->id()]['title'] = [
      '#type' => 'item',
      '#markup' => "<h2>".$currentCategory->field_at_category_user_title->value."</h2>",
      '#prefix' => '<div class="buddy-user-profile-category-container">',
    ];*/

    /*
    $html = '<div class="row">
  <div class="col-12 col-lg-4">'.$currentCategory->field_at_category_user_descript->value.'</div>
  <div class="col-12 col-lg-8"><h2>'.$currentCategory->field_at_category_user_title->value.'</h2>';

    $form['category_container_' . $categoryContainerId]['category_' . $currentCategory->id()]['description'] = [
      '#type' => 'item',
      '#prefix' => '<div class="buddy-user-profile-category-container">',
      '#markup' => $html,
    ];

    */


    if ($currentCategory->field_at_category_user_question->value) {
      $form['category_container_' . $categoryContainerId]['category_' . $currentCategory->id()]["cat_".$currentCategory->id()] = array(
        '#type' => 'radios',
        '#title' => $currentCategory->field_at_category_user_question->value,
        '#default_value' => $selectedAtCategories[$currentCategory->id()],
        '#options' => array(
          '100' => $this->t('Yes'),
          '0' => $this->t('No'),
          '50' => $this->t('It depends'),
        ),
        '#prefix' => $prefixHTML,
        '#suffix' => '</div>
</div></div>',


      );
    }else{
      $form['category_container_' . $categoryContainerId]['category_' . $currentCategory->id()]["cat_".$currentCategory->id()] = array(
        '#type' => 'radios',
        '#title' => $this->t("Do you want support for")." ".$currentCategory->field_at_category_user_title->value."?",
        '#default_value' => $selectedAtCategories[$currentCategory->id()],
        '#options' => array(
          '100' => $this->t('Yes'),
          '0' => $this->t('No'),
          '50' => $this->t('It depends'),
        ),

        '#prefix' => $prefixHTML,
        '#suffix' => '</div>
</div></div>',
      );
    }



    // Group submit handlers in an actions element with a key of "actions" so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form. This is not required, but is convention.
    $form['actions'] = [
      '#type' => 'actions',
      '#prefix' => '<div class="buddy-user-profile-navigation">',
      '#suffix' => '</div>',
    ];

    if ($currentPage > 0 || $currentCategoryNumber > 0) {
      $form['actions']['prev'] = [
        '#type' => 'submit',
        '#value' => $this->t('Back'),
        '#submit' => ['::prevSubmitForm'],
        '#ajax' => [
          'wrapper' => 'user-entry-form-wrapper',
          'callback' => '::prompt',
          'effect' => 'fade',
          'speed' => 500
        ],
        '#attributes' =>
          ['class' => ['buddy_menu_button','buddy-icon-button','buddy-icon-before'],
            'icon' => "fa-arrow-left",
          ]
      ];


    }else{
      $form['actions']['prev'] = [
        '#type' => 'submit',
        '#value' => $this->t('Back'),
        '#submit' => ['::backSubmit'],
        '#attributes' =>
          ['class' => ['buddy_menu_button','buddy-icon-button','buddy-icon-before'],
            'icon' => "fa-arrow-left",
          ]
      ];



    }

    if($currentPage !== count($sortedCategories)-1 || $currentCategoryNumber != count($sortedCategories[$currentPage]['categories'])-1){
      $form['actions']['next'] = [
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => $this->t('Next'),
        '#submit' => ['::nextSubmitForm'],
        '#ajax' => [
          'wrapper' => 'user-entry-form-wrapper',
          'callback' => '::prompt',
          'effect' => 'fade',
          'speed' => 500
        ],
        '#attributes' =>
          ['class' => ['buddy_menu_button','buddy-icon-button','buddy-icon-after'],
            'icon' => "fa-arrow-right",
          ],
      ];


    }else{

      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Finish'),
        '#attributes' =>
          ['class' => ['buddy_menu_button','buddy-icon-button','buddy-icon-after'],
            'icon' => "fa-check",
          ],
      ];


    }

    $form['#prefix'] = '<div id="user-entry-form-wrapper">';
    $form['#suffix'] = '</div>';
    $form['#attached']['library'][] = 'buddy/user_profile_forms';
    return $form;
  }


  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    $values = $this->getSelectedCategories($form,$form_state);

    $selectedAtCategories = $form_state->get('selectedAtCategories');
    foreach ($values as $value){

      $selectedAtCategories[$value['id']] = $value['value'];
    }
    $form_state->set('selectedAtCategories', $selectedAtCategories);


    $storage = \Drupal::service('entity_type.manager')->getStorage('node');

    $userNeedAssignmentIDs = $storage->getQuery()
      ->condition('type', 'user_need_assignment')
      ->condition('field_user_need_ass_user_profile',  $form_state->get('user_profile_id'))
      ->execute();

    $userNeedAssignments = $storage->loadMultiple($userNeedAssignmentIDs);

    foreach ($userNeedAssignments as $userNeedAssignment){
      $userNeedAssignment->delete();
    }


    $user = \Drupal::currentUser();

    $selectedAtCategories = $form_state->get('selectedAtCategories');

    $userNeeds = [];
    foreach ($selectedAtCategories as $categoryID => $percent){

      $node = Node::create([
        'type'        => 'user_need_assignment',
        'title'       =>  'User Need Ass:'.$categoryID.'-'.$user->id().':'.$percent,
        'field_user_need_ass_user_profile' => ['target_id' => $form_state->get('user_profile_id')],
        'field_user_need_ass_support_cat' => ['target_id' => $categoryID],
        'field_user_need_ass_percentage' => ['value' => $percent],
      ]);
      $node->save();


      $userNeeds[] = ['target_id' => $node->id()];
    }

    $userProfile = Node::load($form_state->get('user_profile_id'));
    $profileFinished = $userProfile->get("field_user_profile_finished")->getValue();
    $profileUpdated = false;
    if($profileFinished[0]['value']){
      $profileUpdated = true;
    }
    $userProfile->field_user_profile_user_needs  = $userNeeds;
    $userProfile->field_user_profile_finished = ['value' => true];
    $userProfile->save();

    if($profileUpdated){
      \Drupal::messenger()->addMessage($this->t("Your preferences were updated!"));
      $form_state->setRedirect('buddy.user_profile_overview');
    }else{
      \Drupal::messenger()->addMessage($this->t("Setup complete! Welcome to Buddy!"));
      $form_state->setRedirect('<front>');
    }




  }

  public function nextSubmitForm(array &$form, FormStateInterface $form_state)
  {

    $form_state->set('progress', $form_state->get('progress')+1);
    $values = $this->getSelectedCategories($form,$form_state);


    $selectedAtCategories = $form_state->get('selectedAtCategories');
    foreach ($values as $value){

      $selectedAtCategories[$value['id']] = $value['value'];
    }

    $form_state->set('selectedAtCategories', $selectedAtCategories);



    $categories =  $form_state->get('categories');
    $currentCategoryContainer = $form_state->get('category_container_num');
    $currentCategory = $form_state->get('category_num');
    if($currentCategory == count($categories[$currentCategoryContainer]['categories'])-1){
      $currentCategoryContainer++;
      $currentCategory = 0;
    }else{

      $currentCategory++;
    }


    $form_state->set('category_container_num', $currentCategoryContainer)
      ->set('category_num', $currentCategory)
      ->setRebuild(TRUE);

  }

  public function backSubmit(array &$form, FormStateInterface $form_state){
    $form_state->setRedirect("buddy.user_profile");
  }

  public function prevSubmitForm(array &$form, FormStateInterface $form_state)
  {
    $form_state->set('progress', $form_state->get('progress')-1);
    $values = $this->getSelectedCategories($form,$form_state);

    $selectedAtCategories = $form_state->get('selectedAtCategories');
    foreach ($values as $value){

      $selectedAtCategories[$value['id']] = $value['value'];
    }
    $form_state->set('selectedAtCategories', $selectedAtCategories);


    $categories =  $form_state->get('categories');
    $currentCategoryContainer = $form_state->get('category_container_num');
    $currentCategory = $form_state->get('category_num');
    if($currentCategory == 0){
      $currentCategoryContainer--;
      $currentCategory =  count($categories[$currentCategory]['categories'])-1;
    }else{
      $currentCategory--;

    }

    $form_state->set('category_container_num', $currentCategoryContainer)
      ->set('category_num', $currentCategory)
      ->setRebuild(TRUE);
  }




  /**
   * UserEntryPoint Ajax callback function.
   *
   * @param array $form
   *   Form API form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form API form.
   */
  public function prompt(array $form, FormStateInterface $form_state) {
    $currentTitle = $form_state->get('current_title');
    $categoryCount = $form_state->get('category_count');
    $progress = $form_state->get('progress');

    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#user-entry-form-wrapper',$form));
    $response->addCommand(new InvokeCommand(NULL, 'user_profile_ajax_callback', [$currentTitle]));
    $response->addCommand(new InvokeCommand(NULL, 'user_profile_update_progress_ajax_callback', [$progress/$categoryCount]));





    return $response;
  }

  protected function getSelectedCategories(array &$form, FormStateInterface $form_state){
    $values = $form_state->getValues();
    $selectedCategories = array();

    foreach ($values as $key => $value){

      if(str_starts_with ($key,"cat_")){

        $id = intval(str_replace("cat_","",$key));
        $selectedCategories[] = [
          'id' => $id,
          'value' => $value
        ];


      }

    }

    return $selectedCategories;
  }

}


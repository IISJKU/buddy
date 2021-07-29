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
      ->sort('field_category_description', 'DESC')
      ->execute();

    $atCategories = $storage->loadMultiple($atCategoryIDs);


    if (!$form_state->has('page_num')) {


      $selectedAtCategories = [];

      foreach ($atCategories as $key=> $atCategory){

        $selectedAtCategories[$key] = 0;
        $form_state->set('selectedAtCategories', $selectedAtCategories);
      }




      $form_state->set('page_num', 0);


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
    $currentPage = $form_state->get('page_num');
    $keys = array_keys($atCategoryContainers);
    $categoryContainerId = $keys[$currentPage];
    $atCategoryContainer = $atCategoryContainers[$categoryContainerId];




    if ($atCategoryContainer->field_category_container_user_ti->value) {

      Util::setTitle($atCategoryContainer->field_category_container_user_ti->value);
      $form_state->set('current_title',$atCategoryContainer->field_category_container_user_ti->value);

    }else{

      Util::setTitle($atCategoryContainer->title->value);
      $form_state->set('current_title',$atCategoryContainer->title->value);
    }



    if ($atCategoryContainer->field_category_container_descrip->value) {

      $form['category_container_' . $categoryContainerId]['container_description'] = array(
        '#type' => 'markup',
        '#markup' => $atCategoryContainer->field_category_container_descrip->value,
      );
    }


    foreach ($atCategories as $categoryID => $category) {


      if ($atCategoryContainer->id() == $category->get('field_at_category_container')->target_id) {

        $form['category_container_' . $categoryContainerId]['category_' . $categoryID]['title'] = [
          '#type' => 'item',
          '#markup' => "<h2>".$category->field_at_category_user_title->value."</h2>",
        ];

        $form['category_container_' . $categoryContainerId]['category_' . $categoryID]['description'] = [
          '#type' => 'item',
          '#markup' => "<p>".$category->field_at_category_user_descript->value."</p>",
        ];

        $form['category_container_' . $categoryContainerId]['category_' . $categoryID]["cat_".$categoryID] = array(
          '#type' => 'radios',
          '#title' => $this->t("Do you want ")." ".$category->field_at_category_user_title->value."?",
          '#default_value' => $selectedAtCategories[$categoryID],
          '#options' => array(
            '100' => $this->t('Yes'),
            '0' => $this->t('No'),
          ),


        );

        $form['category_container_' . $categoryContainerId]['category_' . $categoryID]['line'] = [
          '#type' => 'item',
          '#markup' => "<hr>",
        ];

        /*
        $form['category_container_' . $categoryContainerId]['category_' . $categoryID] = array(
          '#type' => 'checkboxes',
          '#title' => $category->title->value,
          '#options' => array(
            $categoryID => $category->field_category_description->value,
          ),
        );
        */
      }
    }


    // Group submit handlers in an actions element with a key of "actions" so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form. This is not required, but is convention.
    $form['actions'] = [
      '#type' => 'actions',
    ];

    if ($currentPage > 0) {
      $form['actions']['prev'] = [
        '#type' => 'submit',
        '#value' => $this->t('Previous step'),
        '#submit' => ['::prevSubmitForm'],
        '#ajax' => [
          'wrapper' => 'user-entry-form-wrapper',
          'callback' => '::prompt',
        ],
      ];
    }

    if($currentPage !== count($atCategoryContainers)-1){
      $form['actions']['next'] = [
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => $this->t('Next'),
        // Custom submission handler for page 1.
        '#submit' => ['::nextSubmitForm'],
        // Custom validation handler for page 1.
    //    '#validate' => ['::pageOneSubmitValidate'],
        '#ajax' => [
          'wrapper' => 'user-entry-form-wrapper',
          'callback' => '::prompt',
        ],

      ];
    }else{

      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Submit'),
      ];
    }




    $form['#prefix'] = '<div id="user-entry-form-wrapper">';
    $form['#suffix'] = '</div>';
    $form['#attached']['library'][] = 'buddy/user_profile_forms';
    return $form;
  }


  public function submitForm(array &$form, FormStateInterface $form_state)
  {


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
    $userProfile->field_user_profile_user_needs  = $userNeeds;
    $userProfile->save();


    $form_state->setRedirect('buddy.at_entry_overview');


  }

  public function nextSubmitForm(array &$form, FormStateInterface $form_state)
  {

    $values = $this->getSelectedCategories($form,$form_state);


    $selectedAtCategories = $form_state->get('selectedAtCategories');
    foreach ($values as $value){

      $selectedAtCategories[$value['id']] = $value['value'];
    }

    $form_state->set('selectedAtCategories', $selectedAtCategories);




    $currentPages = $form_state->get('page_num');
    $currentPages++;

    $form_state->set('page_num', $currentPages)->setRebuild(TRUE);

  }

  public function prevSubmitForm(array &$form, FormStateInterface $form_state)
  {

    $values = $this->getSelectedCategories($form,$form_state);

    $selectedAtCategories = $form_state->get('selectedAtCategories');
    foreach ($values as $value){

      $selectedAtCategories[$value['id']] = $value['value'];
    }
    $form_state->set('selectedAtCategories', $selectedAtCategories);


    $currentPages = $form_state->get('page_num');
    $currentPages--;

    $form_state->set('page_num', $currentPages);
    $form_state->set('page_num', $currentPages)->setRebuild(TRUE);
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
    $response = new AjaxResponse();
    $response->addCommand(new InvokeCommand(NULL, 'myAjaxCallback', [$currentTitle]));
    $response->addCommand(new ReplaceCommand('#user-entry-form-wrapper',$form));
    /*
    $step_no = $form_state->getValue('step');
    switch ($step_no) {
      case UserEntryWizardStep::LoginForm:
        $response = new AjaxResponse();
        $url = Url::fromRoute('user.login');
        $command = new RedirectCommand($url->toString());
        $response->addCommand($command);
        return $response;
      case UserEntryWizardStep::RegisterForm:
        $response = new AjaxResponse();
        $url = Url::fromRoute('user.register');
        $command = new RedirectCommand($url->toString());
        $response->addCommand($command);
        return $response;
    }*/
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


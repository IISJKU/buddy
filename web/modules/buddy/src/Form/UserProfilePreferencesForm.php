<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Util\Util;
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
    if (!$form_state->has('page_num')) {

      $form_state->set('page_num', 0);
    }


    $storage = \Drupal::service('entity_type.manager')->getStorage('node');

    $atCategoryContainersIDs = $storage->getQuery()
      ->condition('type', 'at_category_container')
      ->condition('status', 1)
      ->sort('field_category_container_weight', 'DESC')
      ->execute();

    $atCategoryContainers = $storage->loadMultiple($atCategoryContainersIDs);

    $atCategoryIDs = $storage->getQuery()
      ->condition('type', 'at_category')
      ->condition('status', 1)
      ->sort('field_category_description', 'DESC')
      ->execute();

    $atCategories = $storage->loadMultiple($atCategoryIDs);


    $currentPage = $form_state->get('page_num');
    $keys = array_keys($atCategoryContainers);
    $categoryContainerId = $keys[$currentPage];
    $atCategoryContainer = $atCategoryContainers[$categoryContainerId];



    $form['category_container_' . $categoryContainerId] = array(
      '#type' => 'fieldset',
      '#title' => $this->t($atCategoryContainer->title->value),
    );

    if ($atCategoryContainer->field_category_container_descrip->value) {

      $form['category_container_' . $categoryContainerId]['container_description'] = array(
        '#type' => 'markup',
        '#markup' => $atCategoryContainer->field_category_container_descrip->value,
      );
    }


    foreach ($atCategories as $categoryID => $category) {


      if ($atCategoryContainer->id() == $category->get('field_at_category_container')->target_id) {


        $form['category_container_' . $categoryContainerId]['category_' . $categoryID] = array(
          '#type' => 'checkboxes',
          '#title' => $category->title->value,
          '#options' => array(
            $categoryID => $category->field_category_description->value,
          ),
        );

      }
    }


    // Group submit handlers in an actions element with a key of "actions" so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form. This is not required, but is convention.
    $form['actions'] = [
      '#type' => 'actions',
    ];

    if($currentPage !== count($atCategoryContainers)-1){
      $form['actions']['next'] = [
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => $this->t('Next'),
        // Custom submission handler for page 1.
        // '#submit' => ['::pageOneSubmit'],
        // Custom validation handler for page 1.
        '#validate' => ['::pageOneSubmitValidate'],
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
    return $form;
  }


  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    $currentPages = $form_state->get('page_num');
    $currentPages++;

    $form_state->set('page_num', $currentPages);
    $form_state
      /*  ->set('page_values', [
          // Keep only first step values to minimize stored data.
          'type' => $form_state->getValue('type'),
        ])*/
      //   ->set('page_num', 2)
      // Since we have logic in our buildForm() method, we have to tell the form
      // builder to rebuild the form. Otherwise, even though we set 'page_num'
      // to 2, the AJAX-rendered form will still show page 1.
      ->setRebuild(TRUE);

      return;
    $page_values = $form_state->get('page_values');
    $id = 0;
    if ($page_values['type'] == "software") {

      $id = $this->saveSoftwareType($form, $form_state);
    } else if ($page_values['type'] == "app") {
      $id = $this->saveAppTypeForm($form, $form_state);
    } else {
      //browser_extension
      $id = $this->saveBrowserExtensionType($form, $form_state);

    }


    $this->atEntry->field_at_types[] = ['target_id' => $id];
    $this->atEntry->save();
    $form_state->setRedirect('buddy.at_entry_overview');


  }


  public function pageOneSubmitValidate(array &$form, FormStateInterface $form_state)
  {


  }


  public function pageOneSubmit(array &$form, FormStateInterface $form_state)
  {

    $currentPages = $form_state->get('page_num');
    $currentPages++;

    $form_state->set('page_num', $currentPages);
    $form_state
      /*  ->set('page_values', [
          // Keep only first step values to minimize stored data.
          'type' => $form_state->getValue('type'),
        ])*/
      //   ->set('page_num', 2)
      // Since we have logic in our buildForm() method, we have to tell the form
      // builder to rebuild the form. Otherwise, even though we set 'page_num'
      // to 2, the AJAX-rendered form will still show page 1.
      ->setRebuild(TRUE);
  }


  public function pageTwoForm(array &$form, FormStateInterface $form_state)
  {

    $page_values = $form_state->get('page_values');


    if ($page_values['type'] == "software") {

      $form = $this->createSoftwareTypeForm($form, $form_state);
    } else if ($page_values['type'] == "app") {
      $form = $this->createAppTypeForm($form, $form_state);
    } else {
      //browser_extension
      $form = $this->createBrowserExtensionTypeForm($form, $form_state);

    }


    $form['back'] = [
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      // Custom submission handler for 'Back' button.
      '#submit' => ['::pageTwoBackSubmit'],
      // We won't bother validating the required 'color' field, since they
      // have to come back to this page to submit anyway.
      '#limit_validation_errors' => [],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Submit'),
    ];

    return $form;
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
    return $form;
  }

}

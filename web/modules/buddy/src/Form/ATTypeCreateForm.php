<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Util\Util;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

class ATTypeCreateForm extends FormBase
{

  protected $atEntry;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_api_example_multistep_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state,NodeInterface $atEntry=NULL) {
    $this->atEntry = $atEntry;


    Util::setTitle($this->t("Create type for:").$this->atEntry->getTitle());

    if ($form_state->has('page_num') && $form_state->get('page_num') == 2) {
      return self::pageTwoForm($form, $form_state);
    }


    $form_state->set('page_num', 1);

    $form['description'] = [
      '#type' => 'item',
      '#title' => $this->t('Please select the type of your AT'),
    ];

    $form['type'] = array(
      '#type' => 'radios',
      '#title' => $this
        ->t('Type'),
      '#default_value' => "software",
      '#options' => array(
        "software" => $this->t('Desktop software'),
        "browser_extension" => $this->t('Browser extension'),
        "app" => $this->t('Mobile application'),
      ),
      '#description' => $this->t('Enter the type of assistive technology.')
    );


    // Group submit handlers in an actions element with a key of "actions" so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form. This is not required, but is convention.
    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['back'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Back'),
      '#submit' => ['::backFormSubmit'],
      '#limit_validation_errors' => [],
      '#attributes' => ['class' => ['buddy_link_button buddy_button']],
    ];

    $form['actions']['next'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Next'),
      // Custom submission handler for page 1.
      '#submit' => ['::pageOneSubmit'],
      // Custom validation handler for page 1.
      '#validate' => ['::pageOneSubmitValidate'],
      '#attributes' => ['class' => ['buddy_link_button buddy_button']],
    ];




    return $form;
  }


  public function submitForm(array &$form, FormStateInterface $form_state) {
    $page_values = $form_state->get('page_values');
    $id = 0;
    if($page_values['type']== "software"){

      $id = $this->saveSoftwareType($form,$form_state);
    }else if($page_values['type']== "app"){
      $id = $this->saveAppTypeForm($form,$form_state);
    }else{
      //browser_extension
      $id = $this->saveBrowserExtensionType($form,$form_state);

    }



    $this->atEntry->field_at_types[] =  ['target_id' => $id];
    $this->atEntry->save();


    $route_name = \Drupal::routeMatch()->getRouteName();
    if($route_name == "buddy.at_moderator_type_create_form"){
      $path = Url::fromRoute('buddy.at_moderator_at_entry_overview',
        ['atEntry' =>$this->atEntry->id()])->toString();
      $response = new \Symfony\Component\HttpFoundation\RedirectResponse($path);
      $response->send();
    }else{
      $form_state->setRedirect('buddy.at_entry_overview');
    }



  }
  public function backFormSubmit(array &$form, FormStateInterface $form_state)
  {

    $route_name = \Drupal::routeMatch()->getRouteName();
    if($route_name == "buddy.at_moderator_type_create_form"){
      $path = Url::fromRoute('buddy.at_moderator_at_entry_overview',
        ['atEntry' =>$this->atEntry->id()])->toString();
      $response = new \Symfony\Component\HttpFoundation\RedirectResponse($path);
      $response->send();
    }else{
      $form_state->setRedirect('buddy.at_entry_overview');
    }
  }

  public function pageOneSubmitValidate(array &$form, FormStateInterface $form_state) {


  }


  public function pageOneSubmit(array &$form, FormStateInterface $form_state) {
    $form_state
      ->set('page_values', [
        // Keep only first step values to minimize stored data.
        'type' => $form_state->getValue('type'),
      ])
      ->set('page_num', 2)
      // Since we have logic in our buildForm() method, we have to tell the form
      // builder to rebuild the form. Otherwise, even though we set 'page_num'
      // to 2, the AJAX-rendered form will still show page 1.
      ->setRebuild(TRUE);
  }


  public function pageTwoForm(array &$form, FormStateInterface $form_state) {

    $page_values = $form_state->get('page_values');


    if($page_values['type']== "software"){

      $form = $this->createSoftwareTypeForm($form,$form_state);
    }else if($page_values['type']== "app"){
      $form = $this->createAppTypeForm($form,$form_state);
    }else{
      //browser_extension
      $form = $this->createBrowserExtensionTypeForm($form,$form_state);

    }


    $form['back'] = [
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      '#weight' => 999999,
      // Custom submission handler for 'Back' button.
      '#submit' => ['::pageTwoBackSubmit'],
      // We won't bother validating the required 'color' field, since they
      // have to come back to this page to submit anyway.
      '#limit_validation_errors' => [],
      '#attributes' => ['class' => ['buddy_link_button buddy_button']],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#weight' => 999999,
      '#value' => $this->t('Submit'),
      '#attributes' => ['class' => ['buddy_link_button buddy_button']],
    ];

    return $form;
  }

  public function pageTwoBackSubmit(array &$form, FormStateInterface $form_state) {
    $form_state
      // Restore values for the first step.
      ->setValues($form_state->get('page_values'))
      ->set('page_num', 1)
      // Since we have logic in our buildForm() method, we have to tell the form
      // builder to rebuild the form. Otherwise, even though we set 'page_num'
      // to 1, the AJAX-rendered form will still show page 2.
      ->setRebuild(TRUE);
  }


  public function createSoftwareTypeForm(array $form, FormStateInterface &$form_state,$node=null){

    $form['description'] = [
      '#type' => 'item',
      '#markup' => "<h2>".$this->t('Software Specification')."</h2>",
    ];
    $fields  = Util::getFormFieldsOfContentType("at_type_software",$form, $form_state,$node);
    return array_merge($form, $fields);

  }

  public function createBrowserExtensionTypeForm(array $form, FormStateInterface $form_state,$node=null){
    $form['description'] = [
      '#type' => 'item',
      '#markup' => "<h2>".$this->t('Browser Extension Specification')."</h2>",
    ];
    $fields  = Util::getFormFieldsOfContentType("at_type_browser_extension",$form, $form_state,$node);
    return array_merge($form, $fields);
  }

  public function createAppTypeForm(array $form, FormStateInterface $form_state,$node=null){
    $form['description'] = [
      '#type' => 'item',
      '#markup' => "<h2>".$this->t('Browser Extension Specification')."</h2>",
    ];
    $fields  = Util::getFormFieldsOfContentType("at_type_app",$form, $form_state,$node);
    return array_merge($form, $fields);
  }


  public function saveSoftwareType(array $form, FormStateInterface $form_state){

    $values = $form_state->getValues();

    $nodeDef = [
      'type'        => 'at_type_software',
      'title'       =>  "software_reference",
    ];
    foreach ($values as $fieldName => $value) {
      if (str_starts_with($fieldName, "field_")) {
        $nodeDef[$fieldName] = $values[$fieldName];
      }
    }

    $node = Node::create($nodeDef);
    $node->save();
    return $node->id();

  }

  public function saveBrowserExtensionType(array $form, FormStateInterface $form_state){

    $values = $form_state->getValues();

    $nodeDef = [
      'type'        => 'at_type_browser_extension',
      'title'       =>  "browser extension",
    ];
    foreach ($values as $fieldName => $value) {
      if (str_starts_with($fieldName, "field_")) {
        $nodeDef[$fieldName] = $values[$fieldName];
      }
    }

    $node = Node::create($nodeDef);
    $node->save();
    return $node->id();
  }

  public function saveAppTypeForm(array $form, FormStateInterface $form_state){
    $values = $form_state->getValues();

    $nodeDef = [
      'type'        => 'at_type_app',
      'title'       =>  "app",
    ];
    foreach ($values as $fieldName => $value) {
      if (str_starts_with($fieldName, "field_")) {
        $nodeDef[$fieldName] = $values[$fieldName];
      }
    }

    $node = Node::create($nodeDef);
    $node->save();
    return $node->id();

  }


}

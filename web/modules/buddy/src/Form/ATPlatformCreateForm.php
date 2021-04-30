<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Util\Util;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

class ATPlatformCreateForm extends FormBase
{


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'form_api_example_multistep_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {


    $request = \Drupal::request();
    if ($route = $request->attributes->get(\Symfony\Cmf\Component\Routing\RouteObjectInterface::ROUTE_OBJECT)) {
      $route->setDefault('_title', 'New Title');
    }

    /*
    $form = Util::getFormFieldsOfContentType("at_type_app");

    return $form;
    */
    if ($form_state->has('page_num') && $form_state->get('page_num') == 2) {
      return self::fapiExamplePageTwo($form, $form_state);
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
      '#description' => $this->t('Enter your first name.')
    );


    // Group submit handlers in an actions element with a key of "actions" so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form. This is not required, but is convention.
    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['next'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Next'),
      // Custom submission handler for page 1.
      '#submit' => ['::fapiExampleMultistepFormNextSubmit'],
      // Custom validation handler for page 1.
      '#validate' => ['::fapiExampleMultistepFormNextValidate'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $page_values = $form_state->get('page_values');

    if($page_values['type']== "software"){

      $form = $this->saveSoftwareType($form,$form_state);
    }else if($page_values['type']== "app"){
      $form = $this->saveAppTypeForm($form,$form_state);
    }else{
      //browser_extension
      $form = $this->saveBrowserExtensionType($form,$form_state);

    }
    $this->messenger()->addMessage($this->t('The form has been submitted. name="@type', [
      '@type' => $page_values['type'],
    ]));


  }

  /**
   * Provides custom validation handler for page 1.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function fapiExampleMultistepFormNextValidate(array &$form, FormStateInterface $form_state) {


  }

  /**
   * Provides custom submission handler for page 1.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function fapiExampleMultistepFormNextSubmit(array &$form, FormStateInterface $form_state) {
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

  /**
   * Builds the second step form (page 2).
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The render array defining the elements of the form.
   */
  public function fapiExamplePageTwo(array &$form, FormStateInterface $form_state) {

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
      // Custom submission handler for 'Back' button.
      '#submit' => ['::fapiExamplePageTwoBack'],
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
   * Provides custom submission handler for 'Back' button (page 2).
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function fapiExamplePageTwoBack(array &$form, FormStateInterface $form_state) {
    $form_state
      // Restore values for the first step.
      ->setValues($form_state->get('page_values'))
      ->set('page_num', 1)
      // Since we have logic in our buildForm() method, we have to tell the form
      // builder to rebuild the form. Otherwise, even though we set 'page_num'
      // to 1, the AJAX-rendered form will still show page 2.
      ->setRebuild(TRUE);
  }


  public function createSoftwareTypeForm(array $form, FormStateInterface &$form_state){

    $form['description'] = [
      '#type' => 'item',
      '#markup' => "<h2>".$this->t('Software Specification')."</h2>",
    ];
    $fields  = Util::getFormFieldsOfContentType("at_type_software",$form_state);
    return array_merge($form, $fields);

  }

  public function createBrowserExtensionTypeForm(array $form, FormStateInterface $form_state){
    $form['description'] = [
      '#type' => 'item',
      '#markup' => "<h2>".$this->t('Browser Extension Specification')."</h2>",
    ];
    $fields  = Util::getFormFieldsOfContentType("at_type_browser_extension");
    return array_merge($form, $fields);
  }

  public function createAppTypeForm(array $form, FormStateInterface $form_state){
    $form['description'] = [
      '#type' => 'item',
      '#markup' => "<h2>".$this->t('Browser Extension Specification')."</h2>",
    ];
    $fields  = Util::getFormFieldsOfContentType("at_type_app");
    return array_merge($form, $fields);
  }


  public function saveSoftwareType(array $form, FormStateInterface $form_state){

    $values = $form_state->getValues();


    //* TODO */
    $values2 = $form_state->getUserInput();


    $test = Node::create([
      'type'        => 'at_type_software',
      'title'       =>  "MUH",
    ]);
   // $node->save();

  }

  public function saveBrowserExtensionType(array $form, FormStateInterface $form_state){


  }

  public function saveAppTypeForm(array $form, FormStateInterface $form_state){

    $fields  = Util::getFormFieldsOfContentType("at_type_app");
    return array_merge($form, $fields);
  }
}

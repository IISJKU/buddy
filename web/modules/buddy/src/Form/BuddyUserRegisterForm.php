<?php


namespace Drupal\buddy\Form;


use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\AccountForm;
use Drupal\user\RegisterForm;
use Drupal\Component\Datetime\TimeInterface;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\user\Entity\User;
use Psr\Container\ContainerInterface;

class BuddyUserRegisterForm extends RegisterForm
{
  public function getFormId() {
    return 'buddy_user_register_form';
  }

  public function __construct(EntityRepositoryInterface $entity_repository, LanguageManagerInterface $language_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL) {

    $this->setEntity( \Drupal::entityTypeManager()
      ->getStorage('user')
      ->create([]));
    $this->setModuleHandler(\Drupal::moduleHandler());
    parent::__construct($entity_repository,  $language_manager, $entity_type_bundle_info,  $time);

  }


  public function buildForm(array $form, FormStateInterface $form_state) {


    $html = '<div id="user-entry-social-auth" class="social-auth-container clearfix">';

    global $base_url;
    $module_path = drupal_get_path('module', 'buddy');
    $social_path = $base_url . '/' . $module_path . '/img/social/';

    // Facebook button
    $html .= '<div class="auth-option">';
    $html .= '<a class="social-auth auth-link" href="user/login/facebook">';
    $html .= '<img class="social-auth auth-icon" ';
    $html .= 'src="' . $social_path . 'facebook_logo.svg" alt="';
    $html .= $this->t('Authenticate through Facebook');
    $html .= '"></a></div>';
    // Google button
    $html .= '<div class="auth-option">';
    $html .= '<a class="social-auth auth-link" href="user/login/google">';
    $html .= '<img class="social-auth auth-icon" ';
    $html .= 'src="' . $social_path . 'google_logo.svg" alt="';
    $html .= $this->t('Authenticate through Google');
    $html .= '"></a></div>';

    $html .= '</div><hr>';


    $form['federalized_buttons'] = [
      '#type' => 'markup',
      '#title' => t('Choose a Login provider:'),
      '#markup' => $html,

    ];

    $form['account'] = parent::buildForm(array(),$form_state);



    $form['account'] ['back'] = [
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      '#weight' => 2,
      // Custom submission handler for 'Back' button.
      '#submit' => ['::userLoginBackSubmit'],
      // We won't bother validating the required 'color' field, since they
      // have to come back to this page to submit anyway.
      '#limit_validation_errors' => [],
    ];
    $form['account']['back']['#attributes']['class'][] = 'buddy-icon-button';
    $form['account']['back']['#attributes']['class'][] = 'buddy-icon-before';
    $form['account']['back']['#attributes']['icon'] = "fa-arrow-left";
    $form['#attached']['library'][] = 'buddy/user_profile_forms';
    return $form;


  }

  public function userLoginBackSubmit(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('buddy.user_entry_point',["back"=>"true"]);
  }
}

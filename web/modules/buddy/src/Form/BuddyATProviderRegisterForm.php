<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Util\Util;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\AccountForm;
use Drupal\user\RegisterForm;
use Drupal\Component\Datetime\TimeInterface;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\user\Entity\User;
use Psr\Container\ContainerInterface;

class BuddyATProviderRegisterForm extends RegisterForm
{
  public function getFormId() {
    return 'buddy_atprovider_register_form';
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
    $html .= '<a class="social-auth auth-link" href="user/login/facebook?destination=/node/1">';
    $html .= '<img class="social-auth auth-icon" ';
    $html .= 'src="' . $social_path . 'facebook_logo.svg" alt="';
    $html .= $this->t('Authenticate through Facebook');
    $html .= '"></a></div>';
    // Google button
    $html .= '<div class="auth-option">';
    $html .= '<a class="social-auth auth-link" href="user/login/google?destination=/node/1">';
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



    /*
    $form['google_login'] = array(
      '#type' => 'image_button',
      '#src'  => Util::getBaseURL(false)."/modules/buddy/img/social/google_logo.svg",
      '#value' => 'google',
      '#submit' => ['::socialLoginFormSubmit'],
      '#limit_validation_errors' => [],
      '#attributes' => [
        'class' => ['buddy_social_login_button'],
      ]
    );

    $form['facebook_login'] = array(
      '#type' => 'image_button',
      '#src'  => Util::getBaseURL(false)."/modules/buddy/img/social/facebook_logo.svg",
      '#value' => 'facebook',
      '#submit' => ['::socialLoginFormSubmit'],
      '#limit_validation_errors' => [],
      '#attributes' => [
        'class' => ['buddy_social_login_button'],
      ]
    );


    */
    $form['account'] = parent::buildForm(array(),$form_state);


    $form['#attached']['library'][] = 'buddy/user_profile_forms';
    return $form;


  }
  public function socialLoginFormSubmit(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $_SESSION['buddy']['login'] = "at_provider";

    if(isset($values['google_login'])){

      $form_state->setRedirect('social_auth_google.redirect_to_google');
    }else{

      $form_state->setRedirect('social_auth_facebook.redirect_to_fb');

    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form,$form_state);
  }

}

<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Util\Util;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class BuddyATProviderLoginForm extends \Drupal\user\Form\UserLoginForm
{

  public function getFormId() {
    return 'buddy_at_provider_login_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    if(isset($_SESSION['buddy']['login'])){
      unset($_SESSION['buddy']['login']);
    }
    $tempstore = \Drupal::service('tempstore.private')->get('buddy_module');
    $tempstore->set('login', 'at_provider');

    $tempstore1 = \Drupal::service('tempstore.private')->get('mymodule');
    $tempstore1->set('var_name', 'Just test');


    $asdf = \Drupal::request()->headers->get('referer');

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
    $social_path = $base_url . '/' . $module_path . '/img/social/';
    $form['image_button'] = array(
      '#type'                     => 'image_button',
      '#src'                      => Util::getBaseURL(false)."/modules/buddy/img/icons/app-icon.png", // hope you still have bartik theme
      '#submit' => ['::atProviderGoogleLoginSubmit'],
      '#limit_validation_errors' => [],
    );

    $form = parent::buildForm($form,$form_state);

    $form['back'] = [
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      '#weight' => 2,
      // Custom submission handler for 'Back' button.
      '#submit' => ['::userLoginBackSubmit'],
      // We won't bother validating the required 'color' field, since they
      // have to come back to this page to submit anyway.
      '#limit_validation_errors' => [],
    ];
    $form['back']['#attributes']['class'][] = 'buddy-icon-button';
    $form['back']['#attributes']['class'][] = 'buddy-icon-before';
    $form['back']['#attributes']['icon'] = "fa-arrow-left";

    $form['#attached']['library'][] = 'buddy/user_profile_forms';
    return $form;
  }
  public function validateFinal(array &$form, FormStateInterface $form_state) {
    parent::validateFinal($form,$form_state);

    $errors = $form_state->getErrors();

    if(isset($errors['name'])){
      $form_state->clearErrors();
      $query = isset($user_input['name']) ? ['name' => $user_input['name']] : [];
      $form_state->setErrorByName('name', $this->t('Unrecognized username or password. <a href=":password">Forgot your password?</a>', [':password' => Url::fromRoute('buddy.user_password_form', ["return"=>"user-login"], ['query' => $query])->toString()]));

    }


  }

  public function atProviderGoogleLoginSubmit(array &$form, FormStateInterface $form_state) {

    $_SESSION['buddy']['login'] = "at_provider";

    $form_state->setRedirect('social_auth_google.redirect_to_google');
  }

  public function userLoginBackSubmit(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('buddy.atprovider_entry_point',["back"=>"true"]);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form,$form_state);
  }
}

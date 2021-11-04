<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Util\Util;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\AccountForm;
use Drupal\user\RegisterForm;
use Drupal\Component\Datetime\TimeInterface;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\user\Entity\User;
use Psr\Container\ContainerInterface;

class UserRegisterExternalForm extends FormBase
{


  public function userCreateAccountCancelSubmit(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('buddy.user_entry_point',["back"=>"true"]);
  }

  public function getFormId()
  {
    return "user_register_external_form";
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $html = '<div id="user-entry-social-auth" class="social-auth-container clearfix">';

    global $base_url;
    $module_path = drupal_get_path('module', 'buddy');
    $social_path = $base_url . '/' . $module_path . '/img/social/';

    // Facebook button
    $html .= '<div class="auth-option">';
    $html .= '<a class="buddy_link_button_social_auth" href="user/login/facebook">';
    $html .= '<img class="social-auth auth-icon" src="' . $social_path . 'facebook-logo-480.png" alt="">';
    $html .= $this->t('Register with Facebook');
    $html .= '</a></div>';
    // Google button
    $html .= '<div class="auth-option">';
    $html .= '<a class="buddy_link_button_social_auth" href="user/login/google">';
      $html .= '<img class="social-auth auth-icon" src="' . $social_path . 'google-logo-480.png" alt=">';
    $html .= $this->t('Register with Google');
    $html .= '</a></div>';

    $html .= '</div>';


    $form['federalized_buttons'] = [
      '#type' => 'markup',
      '#title' => t('Choose a Login provider:'),
      '#markup' => $html,

    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
    ];
    $form['actions']['submit']['#attributes']['class'][] = 'buddy_small_link_button back_button';
    $form['#attached']['library'][] = 'buddy/user_profile_forms';

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $form_state->setRedirect('buddy.user_entry_point',["back"=>"true"]);
  }
}

<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Util\Util;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\user\AccountForm;
use Drupal\user\RegisterForm;
use Drupal\Component\Datetime\TimeInterface;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\user\Entity\User;
use Psr\Container\ContainerInterface;

class UserLoginForm extends FormBase
{

  public function getFormId()
  {
    return "user_login_form";
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $form['intro'] = [
      '#type' => 'markup',
      '#markup' => "<div class='login_intro'>".$this->t("How do you want to log in?")."</div>",
      '#allowed_tags' => ['div'],

    ];
    $html = '<div id="user-entry-social-auth" class="social-auth-container clearfix">';

    global $base_url;
    $module_path = drupal_get_path('module', 'buddy');
    $social_path = $base_url . '/' . $module_path . '/img/social/';

    // Facebook button
    $html .= '<div class="auth-option">';
    $html .= '<a class="buddy_link_button_social_auth" href="user/login/facebook">';
    $html .= '<img class="social-auth auth-icon" src="' . $social_path . 'facebook-logo-480.png" alt="">';
    $html .= $this->t('Log in with Facebook');
    $html .= '</a></div>';
    // Google button
    $html .= '<div class="auth-option">';
    $html .= '<a class="buddy_link_button_social_auth" href="user/login/google">';
      $html .= '<img class="social-auth auth-icon" src="' . $social_path . 'google-logo-480.png" alt="">';
    $html .= $this->t('Log in with Google');
    $html .= '</a></div>';


    $localLoginUrl = Url::fromRoute('buddy.user_login_local')->toString();

    // Email button
    $html .= '<div class="auth-option">';
    $html .= '<a class="buddy_link_button_social_auth" href="'.$localLoginUrl.'">';
    $html .= '<img class="social-auth auth-icon" src="' . $social_path . 'email-logo-480.png" alt="">';
    $html .= $this->t('Log in with Email');
    $html .= '</a></div>';

    $html .= '</div>';


    $form['federalized_buttons'] = [
      '#type' => 'markup',
       '#markup' => $html,

    ];

    $markup = "<div class='login_info_registration'>".$this->t("New to Buddy? ");
    $markup.= Link::createFromRoute($this->t('Create account'),'buddy.user_register')->toString()->getGeneratedLink();
    $markup.= "</div>";

    $form['create_account'] = [
      '#type' => 'markup',
      '#markup' => $markup,
      '#allowed_tags' => ['button', 'a', 'div','img','h2','h1','p','b','b','strong','hr'],

    ];


    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#prefix' =>'<div class="auth-option>',
      '#value' => $this->t('Back'),
      '#suffix' => '</div>',
    ];
    $form['actions']['submit']['#attributes']['class'][] = 'buddy_menu_button';
    $form['#attached']['library'][] = 'buddy/user_profile_forms';

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $form_state->setRedirect('<front>',["back"=>"true"]);
  }
}

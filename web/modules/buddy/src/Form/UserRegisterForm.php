<?php

namespace Drupal\buddy\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

/**
 * Implements the UserEntryPoint form controller.
 * Wizard for user log-in and registration
 */
class UserRegisterForm extends FormBase {

  public function getFormId() {
    return 'user_register_form';
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $form = new static($container);
    $form->setStringTranslation($container->get('string_translation'));
    $form->setMessenger($container->get('messenger'));
    return $form;
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {


    $form['intro'] = [
      '#type' => 'markup',
      '#markup' => "<div class='login_intro'>".$this->t("How would you like to register?")."</div>",
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
    $html .= $this->t('Register with Facebook');
    $html .= '</a></div>';
    // Google button
    $html .= '<div class="auth-option">';
    $html .= '<a class="buddy_link_button_social_auth" href="user/login/google">';
    $html .= '<img class="social-auth auth-icon" src="' . $social_path . 'google-logo-480.png" alt="">';
    $html .= $this->t('Register with Google');
    $html .= '</a></div>';


    $localLoginUrl = Url::fromRoute('buddy.user_login_local')->toString();

    // Email button
    $html .= '<div class="auth-option">';
    $html .= '<a class="buddy_link_button_social_auth" href="'.$localLoginUrl.'">';
    $html .= '<img class="social-auth auth-icon" src="' . $social_path . 'email-logo-480.png" alt="">';
    $html .= $this->t('Register with Email');
    $html .= '</a></div>';

    $html .= '</div>';


    $form['federalized_buttons'] = [
      '#type' => 'markup',
      '#markup' => $html,

    ];

    $markup = "<div class='login_info_registration'>".$this->t("Already have an account?");
    $markup.= " ".Link::createFromRoute($this->t('Log in'),'buddy.user_login')->toString()->getGeneratedLink();
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
    $form['actions']['submit']['#attributes']['class'][] = 'buddy_link_button_social_auth back_button';
    $form['#attached']['library'][] = 'buddy/user_profile_forms';

    return $form;

  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    $form_state->setRedirect('<front>');

  }


}

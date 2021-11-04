<?php

namespace Drupal\buddy\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

/**
 * Implements the UserEntryPoint form controller.
 * Wizard for user log-in and registration
 */
class UserEntryPoint extends FormBase {

  public function getFormId() {
    return 'user_entry_point';
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
    $form['steps'] = [
      '#type' => 'markup',
      '#markup' => "<div class='steps'>".$this->t("Step 1 out of 2")."</div>",
      '#allowed_tags' => ['button', 'a', 'div', 'img', 'h2', 'h1', 'p', 'b', 'b', 'strong', 'hr'],

    ];

    $form['login_op'] = array(
      '#type' => 'radios',
      '#title' => $this
        ->t('How do you want to register?'),
      '#default_value' =>  0,
      '#options' => array(
        0 => $this
          ->t('Register with email.'),
        1 => $this
          ->t('Register with Facebook or Google.'),
      ),
      '#required' => TRUE,
    );

    $form['actions'] = [
      '#type' => 'actions',
    ];

    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
    ];

    $form['actions']['submit']['#attributes']['class'][] = 'buddy-icon-button';
    $form['actions']['submit']['#attributes']['icon'] = "fa-arrow-right";
    $form['#attached']['library'][] = 'buddy/user_profile_forms';
    return $form;

  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $login_op = $form_state->getValue('login_op');

    switch ($login_op) {
      case 0:{
        $form_state->setRedirect('buddy.user_entry_email');
        break;
      }
      case 1:{
        $form_state->setRedirect('buddy.user_register');
        break;
      }
      /*
      case 2:{
        $form_state->setRedirect('buddy.user_password_form',["return"=>"entry"]);
        break;
      }
      */
    }
  }


}

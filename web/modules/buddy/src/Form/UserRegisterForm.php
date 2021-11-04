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
    $form['steps'] = [
      '#type' => 'markup',
      '#markup' => "<div class='steps'>".$this->t("Step 1 out of 2")."</div>",
      '#allowed_tags' => ['div'],

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

    $backLink = Link::createFromRoute($this->t('Back'),'<front>',[],['attributes' => ['class' => 'btn btn-primary buddy_small_link_button back_button']])->toString()->getGeneratedLink();

    $form['back_link'] = [
      '#type' => 'markup',
      '#markup' => $backLink,
      '#allowed_tags' => ['button', 'a', 'div', 'img', 'h2', 'h1', 'p', 'b', 'b', 'strong', 'hr'],

    ];


    // Add a submit button that handles the submission of the form.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
    ];

    $form['submit']['#attributes']['class'][] = 'buddy_small_link_button';
    $form['#attached']['library'][] = 'buddy/user_profile_forms';


    return $form;

  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $login_op = $form_state->getValue('login_op');

    switch ($login_op) {
      case 0:{
        $form_state->setRedirect('buddy.user_register_local');
        break;
      }
      case 1:{
        $form_state->setRedirect('buddy.user_register_external');
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

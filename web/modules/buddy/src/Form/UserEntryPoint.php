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

    $form['#tree'] = TRUE;
    $form['description'] = [
      '#markup' => $this->t('Welcome to Buddy!'),
    ];

    $form['step'] = [
      '#type' => 'value',
      '#value' => !empty($form_state->getValue('step')) ?
        $form_state->getValue('step') :
        UserEntryWizardStep::LoginOrRegister,
    ];

    // Local step (always increase or decrease by one)
    $form['step_local'] = [
      '#type' => 'value',
      '#value' => !empty($form_state->getValue('step_local')) ?
        $form_state->getValue('step_local') :
        1,
    ];

    $step = 'step' . $form['step']['#value'];

    $form[$step] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Step @step:',
        array('@step' => $form['step_local']['#value'])),
      '#tree' => TRUE,
    ];

    switch ($form['step']['#value']) {
      case UserEntryWizardStep::LoginOrRegister:
        $limit_validation_errors = [['step']];
        $form[$step]['is_login'] = array(
          '#type' => 'radios',
          '#title' => $this
            ->t('Do you already have a Buddy account?'),
          '#default_value' => $form_state->hasValue([$step, 'is_login']) ?
            $form_state->getValue([$step, 'is_login']) : 1,
          '#options' => array(
            1 => $this
              ->t('Yes, I already have an account.'),
            0 => $this
              ->t('No, I would like to create a new account.'),
          ),
          '#required' => TRUE,
        );
        break;

      case UserEntryWizardStep::LoginProvider:
        $limit_validation_errors = [['step'],
          ['step' . UserEntryWizardStep::LoginOrRegister]];
        $form[$step]['login_method'] = array(
          '#type' => 'radios',
          '#title' => $this
            ->t('Choose how to log in:'),
          '#default_value' => $form_state->hasValue([$step, 'login_method']) ?
            $form_state->getValue([$step, 'login_method']) : 'local',
          '#options' => array(
            'federated' => $this
              ->t('Log in with Facebook or Google.'),
            'local' => $this
              ->t('Log in with Buddy account.'),
          ),
          '#required' => TRUE,
        );
        break;

      case UserEntryWizardStep::RegisterProvider:
        $limit_validation_errors = [['step'],
          ['step' . UserEntryWizardStep::LoginOrRegister]];
        $form[$step]['register_method'] = array(
          '#type' => 'radios',
          '#title' => $this
            ->t('Choose how to register:'),
          '#default_value' => $form_state->hasValue([$step, 'register_method']) ?
            $form_state->getValue([$step, 'register_method']) : '',
          '#options' => array(
            'federated' => $this
              ->t('Register with an existing Facebook or Google account.'),
            'local' => $this
              ->t('Create a new Buddy account.'),
          ),
          '#required' => TRUE,
        );
        break;

      case UserEntryWizardStep::FederalizedOptions:

        $limit_validation_errors = [['step'],
          ['step' . UserEntryWizardStep::LoginOrRegister],
          ['step'. UserEntryWizardStep::RegisterProvider],
        ];

        $html = '<div id="user-entry-social-auth" class="social-auth-container">';

        // Facebook button
        $html .= '<div class="auth-option">';
        $html .= '<a class="social-auth auth-link" href="/user/login/facebook">';
        $html .= '<img class="social-auth auth-icon" ';
        $html .= 'src="modules/buddy/img/social/facebook_logo.svg" ';
        $html .= $this->t('alt="Authenticate through Facebook">');
        $html .= '</a></div>';
        // Google button
        $html .= '<div class="auth-option">';
        $html .= '<a class="social-auth auth-link" href="/user/login/google">';
        $html .= '<img class="social-auth auth-icon" ';
        $html .= 'src="modules/buddy/img/social/google_logo.svg" ';
        $html .= $this->t('alt="Authenticate through Google">');
        $html .= '</a></div>';

        $html .= '</div>';

        $form[$step]['federalized_buttons'] = [
          '#type' => 'markup',
          '#title' => t('Choose a Login provider:'),
          '#markup' => $html,
        ];
        break;

      default:
        $limit_validation_errors = [];
    }

    $form['actions'] = ['#type' => 'actions'];
    if ($form['step']['#value'] > 1) {
      $form['actions']['prev'] = [
        '#type' => 'submit',
        '#value' => $this->t('Previous step'),
        '#limit_validation_errors' => $limit_validation_errors,
        '#submit' => ['::prevSubmit'],
        '#ajax' => [
          'wrapper' => 'user-entry-form-wrapper',
          'callback' => '::prompt',
        ],
      ];
    }
    if ($form['step']['#value'] != UserEntryWizardStep::FederalizedOptions &&
      $form['step']['#value'] != UserEntryWizardStep::LoginForm &&
      $form['step']['#value'] != UserEntryWizardStep::RegisterForm) {
      $form['actions']['next'] = [
        '#type' => 'submit',
        '#value' => $this->t('Next step'),
        '#submit' => ['::nextSubmit'],
        '#ajax' => [
          'wrapper' => 'user-entry-form-wrapper',
          'callback' => '::prompt',
          'event' => 'click',
        ],
      ];
    }

    $form['#prefix'] = '<div id="user-entry-form-wrapper">';
    $form['#suffix'] = '</div>';

    return $form;

  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
  }

  /**
   * User login submit.
   *
   * @param array $form
   *   The Form API form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The FormState object.
   *
   * @return array
   *   The Form API form.
   */
  public function loginSubmit(array $form, FormStateInterface $form_state): array {
    // TODO: Implement loginSubmit() method.
    $i = 0;
    return $form;
  }

  /**
   * Ajax callback that moves the form to the previous step.
   *
   * @param array $form
   *   The Form API form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The FormState object.
   *
   * @return array
   *   The Form API form.
   */
  public function prevSubmit(array $form, FormStateInterface $form_state): array {

    $step_no = $form_state->getValue('step');
    $step = 'step' . $step_no;


    switch ($step_no) {
      default:
        $form_state->setValue('step', UserEntryWizardStep::LoginOrRegister);
        break;
    }

    $form_state->setValue('step_local', $form_state->getValue('step_local') - 1);
    $form_state->setRebuild();
    return $form;
  }

  /**
   * Ajax callback that moves the form to the next step and rebuilds the form.
   *
   * @param array $form
   *   The Form API form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The FormState object.
   *
   * @return array
   *   The Form API form.
   */
  public function nextSubmit(array $form, FormStateInterface $form_state) {

    $step_no = $form_state->getValue('step');
    $step = 'step' . $step_no;

    switch ($step_no) {
      case UserEntryWizardStep::LoginOrRegister:
        if ($form_state->getValue([$step, 'is_login'])) {
          $form_state->setValue('step', UserEntryWizardStep::LoginProvider);
        } else {
          $form_state->setValue('step', UserEntryWizardStep::RegisterProvider);
        }
        break;
      case UserEntryWizardStep::LoginProvider:
        if ($form_state->getValue([$step, 'login_method']) == 'federated') {
          $form_state->setValue('step', UserEntryWizardStep::FederalizedOptions);
        } else {
          $form_state->setValue('step', UserEntryWizardStep::LoginForm);
        }
        break;
      case UserEntryWizardStep::RegisterProvider:
        if ($form_state->getValue([$step, 'register_method']) == 'federated') {
          $form_state->setValue('step', UserEntryWizardStep::FederalizedOptions);
        } else {
          $form_state->setValue('step', UserEntryWizardStep::RegisterForm);
        }
        break;
    }

    $form_state->setValue('step_local', $form_state->getValue('step_local') + 1);
    $form_state->setRebuild();
    return $form;
  }

  /**
   * UserEntryPoint Ajax callback function.
   *
   * @param array $form
   *   Form API form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form API form.
   */
  public function prompt(array $form, FormStateInterface $form_state) {
    $step_no = $form_state->getValue('step');
    switch ($step_no) {
      case UserEntryWizardStep::LoginForm:
        $response = new AjaxResponse();
        $url = Url::fromRoute('user.login');
        $command = new RedirectCommand($url->toString());
        $response->addCommand($command);
        return $response;
    }
    return $form;
  }

}

abstract class UserEntryWizardStep
{
  const LoginOrRegister = 1;
  const LoginProvider = 2;
  const RegisterProvider = 3;
  const LoginForm = 4;
  const RegisterForm = 5;
  const FederalizedOptions = 6;
}

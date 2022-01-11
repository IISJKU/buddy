<?php

namespace Drupal\buddy\Form;

use Drupal\buddy\Util\Util;
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
class UserProfileCreationSelectionForm extends FormBase {

  public function getFormId() {
    return 'user_profile_creation_selection_form';
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
    $user = \Drupal::currentUser();
    $user_profileID = \Drupal::entityQuery('node')
      ->condition('type', 'user_profile')
      ->condition('uid', $user->id(), '=')
      ->condition('field_user_profile_finished', true, '=')
      ->execute();
    if (count($user_profileID) == 0) {
      $a = $this->t("Setup preferences");
      Util::setTitle($this->t("Setup preferences")." ");
    }else{
      Util::setTitle($this->t("Update preferences")." ");
    }

    $form['steps'] = [
      '#type' => 'markup',
      '#markup' => "<div class='steps'>".$this->t("In order for Buddy to give you suitable recommendations, we need to know your preferences..")."</div>",
      '#allowed_tags' => ['div'],

    ];
    $form['login_op'] = array(
      '#type' => 'radios',
      '#title' => $this->t('You can chose to:'),
      '#default_value' =>  0,
      '#options' => array(
        0 => $this
          ->t('Answer a few questions'),
        1 => $this
          ->t('Play a game (still in development)'),
      ),
      '#required' => TRUE,
    );

    $form['actions'] = [
      '#type' => 'actions',
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
        $form_state->setRedirect('buddy.user_profile_classic');
        break;
      }
      case 1:{
        $form_state->setRedirect('buddy_profile_wizard.profile_wizard');
        break;
      }
    }
  }


}

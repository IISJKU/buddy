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
      '#markup' => "<div class='row'>
  <div class='col-12 col-lg-6'><div class='profile_step_introduction'>".$this->t("By letting Buddy know about where you need support, it can recommend suitable tools for you.")."</div>
        <p>".$this->t("You can set your preferences in two ways:")."</p>
        <ol class='preference_instructions'>
        <li>".$this->t("You can answer questions about what support you need.")."</li>
        <li>".$this->t("You can also play some games. Buddy will then user artificial intelligence to calculate what support you need.")."</li>
</ol></div></div>",
      '#allowed_tags' => ['div','p','li','ol'],

    ];


    $form['actions'] = [
      '#type' => 'actions',
    ];

    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Answering questions'),
      '#attributes' => ['class' => ['buddy_menu_button_large','buddy_menu_button','buddy_mobile_100']],
      '#prefix' => '<div>',
      '#suffix' => '</div>',
    ];

    $form['actions']['game'] = [
      '#type' => 'submit',
      '#value' => $this->t('Play a game'),
      /*'#weight' => -1, */
      '#submit' => ['::userPlayGameSubmit'],
      '#attributes' => ['class' => ['buddy_menu_button_large','buddy_menu_button','buddy_mobile_100']],
      '#prefix' => '<div>',
      '#suffix' => '</div>',
    ];

    $user = \Drupal::currentUser();
    $user_profileID = \Drupal::entityQuery('node')
      ->condition('type', 'user_profile')
      ->condition('uid', $user->id(), '=')
      ->condition('field_user_profile_finished', true, '=')
      ->execute();
    if (count($user_profileID) == 1) {
      $form['actions']['cancel'] = [
        '#type' => 'submit',
        '#value' => $this->t('Cancel'),
        /*'#weight' => -1, */
        '#submit' => ['::userCancelSubmit'],
        '#attributes' => ['class' => ['buddy_menu_button_large','buddy_menu_button','buddy_invert_button','buddy_mobile_100']],
        '#prefix' => '<div>',
        '#suffix' => '</div>',
      ];
    }


    $form['#attached']['library'][] = 'buddy/user_profile_forms';


    return $form;

  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('buddy.user_profile_classic');

    }

    public function userPlayGameSubmit(array &$form, FormStateInterface $form_state)
    {
      $form_state->setRedirect('buddy_profile_wizard.profile_wizard');
    }

    public function userCancelSubmit(array &$form, FormStateInterface $form_state)
    {
      $form_state->setRedirect('buddy.user_profile_overview');
    }


}

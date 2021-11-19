<?php


namespace Drupal\buddy\Form;


use Drupal\Core\Form\FormStateInterface;

class BuddyUserPasswordForm extends \Drupal\user\Form\UserPasswordForm
{
  public function getFormId() {
    return 'buddy_user_password_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form,$form_state);
    $return = \Drupal::request()->query->get('return');

    $form_state->set('return', $return);
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

  public function userLoginBackSubmit(array &$form, FormStateInterface $form_state) {
    $return = $form_state->get('return');
    if($return == "user-login"){

      $form_state->setRedirect('buddy.user_login');
    }else{
      $form_state->setRedirect('buddy.user_entry_point');
    }
  }
}

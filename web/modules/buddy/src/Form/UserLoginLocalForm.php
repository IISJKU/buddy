<?php


namespace Drupal\buddy\Form;


use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class UserLoginLocalForm extends \Drupal\user\Form\UserLoginForm
{

  public function getFormId()
  {
    return 'user_login_local_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {


    $form = parent::buildForm($form, $form_state);

    $form['actions']['submit']['#attributes']['class'][] = 'buddy_small_link_button';

    $form['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#weight' => -1,
      '#submit' => ['::userLoginLocalCancelSubmit'],
      '#limit_validation_errors' => [],
    ];
    $form['actions']['cancel']['#attributes']['class'][] = 'buddy_small_link_button back_button';

    $form['#attached']['library'][] = 'buddy/user_profile_forms';

    return $form;
  }

  public function validateFinal(array &$form, FormStateInterface $form_state)
  {
    parent::validateFinal($form, $form_state);

    $errors = $form_state->getErrors();

    if (isset($errors['name'])) {
      $form_state->clearErrors();
      $query = isset($user_input['name']) ? ['name' => $user_input['name']] : [];
      $form_state->setErrorByName('name', $this->t('Unrecognized username or password. <a href=":password">Forgot your password?</a>', [':password' => Url::fromRoute('user.pass', [], ['query' => $query])->toString()]));
    }


  }

  public function userLoginLocalCancelSubmit(array &$form, FormStateInterface $form_state)
  {
    $form_state->setRedirect('buddy.user_login', ["back" => "true"]);
  }
}

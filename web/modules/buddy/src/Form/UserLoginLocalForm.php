<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Util\Util;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
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

    $form['#title'] = $this->t("Log in with Email");
    $form['name']['#title'] = $this->t("Email or username");
 //   $form['name']['#description'] = $this->t("Enter your Buddy email or username");
    unset($form['name']['#description'] );
 //   $form['pass']['#description'] = $this->t("Enter the password that accompanies your email or username");
    unset($form['pass']['#description']);
    $form['actions']['submit']['#attributes']['class'][] = 'buddy_menu_button buddy_mobile_100';

    $form['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      /*'#weight' => -1, */
      '#submit' => ['::userLoginLocalCancelSubmit'],
      '#limit_validation_errors' => [],
      '#attributes' => ['class' => ['buddy_menu_button','buddy_invert_button','buddy_mobile_100']]
    ];

    $forgotPWLink = Link::createFromRoute($this->t('Forgot password?'),'user.pass')->toString()->getGeneratedLink();


    $form['actions']['forgot_pass'] = [
      '#type' => 'markup',
      '#markup' => "<div>".$forgotPWLink."</div>",
      '#allowed_tags' => ['div','a'],

    ];




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

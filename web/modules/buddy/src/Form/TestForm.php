<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Util\Util;
use Drupal\Core\Form\FormStateInterface;

class TestForm extends \Drupal\Core\Form\FormBase
{

    /**
     * @inheritDoc
     */
    public function getFormId()
    {
        return "aasdf";
    }

    /**
     * @inheritDoc
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
      $form['google_login'] = array(
        '#type' => 'image_button',
        '#src'  => Util::getBaseURL(false)."/modules/buddy/img/social/google_logo.svg",
        '#value' => 'google',
    //    '#submit' => ['::socialLoginFormSubmit'],
        '#limit_validation_errors' => [],
        '#attributes' => [
          'class' => ['buddy_social_login_button'],
        ]
      );

      $form['facebook_login'] = array(
        '#type' => 'image_button',
        '#src'  => Util::getBaseURL(false)."/modules/buddy/img/social/facebook_logo.svg",
        '#value' => 'facebook',
        //'#submit' => ['::socialLoginFormSubmit'],
        '#limit_validation_errors' => [],
        '#attributes' => [
          'class' => ['buddy_social_login_button'],
        ]
      );

      $form['reini_login'] = array(
        '#type' => 'image_button',
        '#src'  => Util::getBaseURL(false)."/modules/buddy/img/icons/app-icon.png",
        '#value' => 'reini',
        //'#submit' => ['::socialLoginFormSubmit'],
        '#limit_validation_errors' => [],
        '#attributes' => [
          'class' => ['buddy_social_login_button'],
        ]
      );

      return $form;
    }

    /**
     * @inheritDoc
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
      $values = $form_state->getValues();

      $a = 1;
        // TODO: Implement submitForm() method.
    }
  public function socialLoginFormSubmit(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $_SESSION['buddy']['login'] = "at_provider";

    if(isset($values['google_login'])){

      $form_state->setRedirect('social_auth_google.redirect_to_google');
    }else{

      $form_state->setRedirect('social_auth_facebook.redirect_to_fb');

    }
  }
}

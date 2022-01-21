<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Util\Util;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\user\AccountForm;
use Drupal\user\RegisterForm;
use Drupal\Component\Datetime\TimeInterface;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\user\Entity\User;
use Psr\Container\ContainerInterface;

class UserRegisterLocalForm extends RegisterForm
{
  public function getFormId() {
    return 'user_register_local_form';
  }

  public function __construct(EntityRepositoryInterface $entity_repository, LanguageManagerInterface $language_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL) {

    $this->setEntity( \Drupal::entityTypeManager()
      ->getStorage('user')
      ->create([]));
    $this->setModuleHandler(\Drupal::moduleHandler());
    parent::__construct($entity_repository,  $language_manager, $entity_type_bundle_info,  $time);

  }


  public function buildForm(array $form, FormStateInterface $form_state) {

    /*
    $form['steps'] = [
      '#type' => 'markup',
      '#markup' => "<div class='steps'>".$this->t("Step 2 out of 2")."</div>",
      '#allowed_tags' => ['div'],

    ];
    */

    Util::setTitle("Register with email");

    $form['account'] = parent::buildForm(array(),$form_state);

    unset($form['account']['account']['mail']['#description']);
    unset($form['account']['account']['name']['#description']);
    unset($form['account']['account']['pass']['#description']);
    $form['account']['actions']['submit']['#value'] = $this->t("Create account");
    $form['account']['actions']['submit']['#attributes']['class'][] = 'buddy_menu_button buddy_mobile_100';

    $form['account']['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#weight' => 20,
      '#submit' => ['::userCreateAccountCancelSubmit'],
      '#limit_validation_errors' => [],
      '#attributes' => ['class' => ['buddy_menu_button','buddy_invert_button','buddy_mobile_100']]
    ];

    $markup = "<div class='login_info_registration'>".$this->t("Already have an account?");
    $markup.= " ".Link::createFromRoute($this->t('Log in'),'buddy.user_login')->toString()->getGeneratedLink();
    $markup.= "</div>";

    $form['create_account'] = [
      '#type' => 'markup',
      '#markup' => $markup,
      '#weight' => 666,
      '#allowed_tags' => ['button', 'a', 'div','img','h2','h1','p','b','b','strong','hr'],

    ];


    $form['#attached']['library'][] = 'buddy/user_register_login';

    honeypot_add_form_protection($form,$form_state,['honeypot', 'time_restriction']);
    return $form;


  }

  public function userCreateAccountCancelSubmit(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('buddy.user_register',["back"=>"true"]);
  }
}

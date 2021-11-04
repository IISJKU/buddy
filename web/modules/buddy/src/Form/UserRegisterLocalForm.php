<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Util\Util;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Form\FormStateInterface;
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

    $form['steps'] = [
      '#type' => 'markup',
      '#markup' => "<div class='steps'>".$this->t("Step 2 out of 2")."</div>",
      '#allowed_tags' => ['div'],

    ];

    Util::setTitle("Register with email");

    $form['account'] = parent::buildForm(array(),$form_state);
    $form['account']['actions']['submit']['#value'] = $this->t("Register");
    $form['account']['actions']['submit']['#attributes']['class'][] = 'buddy_small_link_button';

    $form['account']['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#weight' => 2,
      '#submit' => ['::userCreateAccountCancelSubmit'],
      '#limit_validation_errors' => [],
    ];
    $form['account']['actions']['cancel']['#attributes']['class'][] = 'buddy_small_link_button back_button';

    $form['#attached']['library'][] = 'buddy/user_profile_forms';
    return $form;


  }

  public function userCreateAccountCancelSubmit(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('buddy.user_entry_point',["back"=>"true"]);
  }
}

<?php


namespace Drupal\buddy\Form;


use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\user\AccountForm;
use Drupal\user\Entity\User;
use Drupal\user\ProfileForm;

class UserAccountForm extends ProfileForm
{
  public function getFormId() {
    return 'buddy_user_account_form';
  }

  public function __construct(EntityRepositoryInterface $entity_repository, LanguageManagerInterface $language_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL) {
    $user = User::load(\Drupal::currentUser()->id());
    $this->setEntity( $user);
    $this->setModuleHandler(\Drupal::moduleHandler());
    parent::__construct($entity_repository,  $language_manager, $entity_type_bundle_info,  $time);

  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $form_state->setRedirect('buddy.user_profile_overview');
  }
}

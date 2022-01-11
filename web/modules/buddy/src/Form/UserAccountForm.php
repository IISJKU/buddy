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


  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);

    // The user account being edited.
    $account = $this->entity;

    $element['delete']['#type'] = 'submit';
    $element['delete']['#value'] = $this->t('Cancel account');
    $element['delete']['#submit'] = ['::cancelAccountSubmit'];
    $element['delete']['#access'] = $account->id() > 1 && $account->access('delete');
    $element['delete']['#attributes'] = ['class' => ['buddy_link_button buddy_button']];
    $element['submit']['#attributes'] = ['class' => ['buddy_link_button buddy_button']];
    return $element;
  }

  public function cancelAccountSubmit($form, FormStateInterface $form_state) {
    $form_state->setRedirect(
      'buddy.user_account_delete_form'
    );
  }
}

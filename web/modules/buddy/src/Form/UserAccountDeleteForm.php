<?php


namespace Drupal\buddy\Form;


use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

class UserAccountDeleteForm extends \Drupal\Core\Form\FormBase
{

    /**
     * @inheritDoc
     */
    public function getFormId()
    {
       return "buddy_user_account_delete_form";
    }

    /**
     * @inheritDoc
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
      $form['description'] = [
        '#type' => 'item',
        '#title' => $this->t('Do you really want to delete your account?'),
      ];

      // Group submit handlers in an actions element with a key of "actions" so
      // that it gets styled correctly, and so that other modules may add actions
      // to the form. This is not required, but is convention.
      $form['actions'] = [
        '#type' => 'actions',
      ];

      // Add a submit button that handles the submission of the form.
      $form['actions']['delete'] = [
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => $this->t('Yes'),
        '#submit' => ['::deleteFormSubmit'],
        '#attributes' => ['class' => ['buddy_link_button buddy_button']],

      ];
      // Add a submit button that handles the submission of the form.
      $form['actions']['no_submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('No'),
        '#attributes' => ['class' => ['buddy_link_button buddy_button']],
      ];
      return $form;
    }

    /**
     * @inheritDoc
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {

      $form_state->setRedirect('buddy.user_account_form');
    }

  public function deleteFormSubmit(array &$form, FormStateInterface $form_state)
  {
    $user = \Drupal::currentUser();
    $userNodes = \Drupal::entityQuery('node')
      ->condition('uid', $user->id(), '=')
      ->execute();


    $storage = \Drupal::service('entity_type.manager')->getStorage('node');
    foreach ($userNodes as $userNodeId){
      $node = Node::load($userNodeId);
      $node->delete();
    }




    $account = User::load($user->id());
    $account->delete();

    $form_state->setRedirect('<front>');


  }
}

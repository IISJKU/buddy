<?php

namespace Drupal\buddy_profile_wizard\Form;

use Drupal\node\Entity\Node;

class ProfileWizardForm extends \Drupal\Core\Form\FormBase
{

    /**
     * @inheritDoc
     */
    public function getFormId()
    {
        return "buddy_profile_wizard";
    }

    /**
     * @inheritDoc
     */
    public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state)
    {

      $form['result'] = array(
        '#type' => 'hidden',
        '#value' => "test",
      );

      $form['description'] = [
        '#type' => 'item',
        '#markup' => "<div id='phaser-container'></div>",
      ];

      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#name' => 'next',
        '#value' => $this->t('Submit'),
        '#attributes' => ['class' => ['buddy_profile_wizard_submit']],
      ];

      $form['#attached']['library'][] = 'buddy_profile_wizard/buddy_profile_wizard';


      return $form;
    }

    /**
     * @inheritDoc
     */
    public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state)
    {
      if( \Drupal::currentUser()->isAuthenticated()){
        $values = $form_state->getValues();

        $user = \Drupal::currentUser();
        $user_profileID = \Drupal::entityQuery('node')
          ->condition('type', 'user_profile')
          ->condition('uid', $user->id(), '=')
          ->execute();


        if(count($user_profileID) == 0){
          $node = Node::create([
            'type'        => 'user_profile',
            'title'       =>  'User Profile:'.$user->id(),

          ]);
          $node->save();

          $user_profileID = $node->id();

        }else {

          $user_profileID =  reset($user_profileID);

        }

        $userProfile = Node::load($user_profileID);
        $userProfile->field_user_profile_finished = ['value' => true];
        $userProfile->save();

      }

      $form_state->setRedirect('<front>');
    }
}

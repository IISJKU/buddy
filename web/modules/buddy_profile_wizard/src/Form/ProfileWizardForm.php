<?php

namespace Drupal\buddy_profile_wizard\Form;

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

      $values = $form_state->getValues();

      $a = 1;

    }
}

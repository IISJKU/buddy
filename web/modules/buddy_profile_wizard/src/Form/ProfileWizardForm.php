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
      $form['description'] = [
        '#type' => 'item',
        '#markup' => "<div id='phaser-container'></div>",
      ];
      $form['#attached']['library'][] = 'buddy_profile_wizard/buddy_profile_wizard';

      return $form;
    }

    /**
     * @inheritDoc
     */
    public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state)
    {

    }
}

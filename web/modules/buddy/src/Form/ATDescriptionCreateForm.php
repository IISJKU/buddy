<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Util\Util;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

class ATDescriptionCreateForm extends FormBase
{
  protected $atEntry;

  public function getFormId()
  {
    return "at_description_create_form";
  }

  public function buildForm(array $form, FormStateInterface $form_state, $atEntry = null)
  {
    $this->atEntry = $atEntry;


    return $this->createForm($form,$form_state);

  }

  protected function createForm(array $form, FormStateInterface $form_state,$atDescription){
    $form['description'] = [
      '#type' => 'item',
      '#markup' => "<h2>".$this->t('Create AT Description')."</h2>",
    ];
    $fields  = Util::getFormFieldsOfContentType("at_description",$form, $form_state,$atDescription);
    $form =  array_merge($form, $fields);

    // Group submit handlers in an actions element with a key of "actions" so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form. This is not required, but is convention.
    $form['actions'] = [
      '#type' => 'actions',
    ];

    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;

  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $values = $form_state->getValues();

    $nodeDef = [
      'type'        => 'at_description',
      'title'       =>  "at description",
    ];
    foreach ($values as $fieldName => $value) {
      if (str_starts_with($fieldName, "field_")) {
        $nodeDef[$fieldName] = $values[$fieldName];
      }
    }

    $node = Node::create($nodeDef);
    try {
      $node->save();
      $this->atEntry->field_at_descriptions[] =  ['target_id' =>  $node->id()];
      $this->atEntry->save();
    } catch (EntityStorageException $e) {

    }


  }
}

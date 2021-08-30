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

  protected function createForm(array $form, FormStateInterface $form_state,$atDescription=null){
    $form['description'] = [
      '#type' => 'item',
      '#markup' => "<h3>".$this->t('A localized description of your assistive technology.')."</h3>",
    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Localized name'),
      '#description' => $this->t('The localized name of your assistive technology'),
      '#required' => TRUE,
    ];
    if($atDescription){
      $form['title']['#default_value'] = $atDescription->getTitle();
    }

    $fields  = Util::getFormFieldsOfContentType("at_description",$form, $form_state,$atDescription);
    foreach ($fields as $key => $field){

      //Remove field at entry.... it is set automatically on node save
      if($field['widget']["#field_name"] == "field_at_entry"){
        unset($fields[$key]);
        break;
      }
    }

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
      'title'       =>  $values['title'],
    ];
    foreach ($values as $fieldName => $value) {
      if (str_starts_with($fieldName, "field_")) {

        if($fieldName === "field_at_description_at_image"){

          $nodeDef[$fieldName] = [
            'target_id' => $values[$fieldName][0]['fids'][0],
            'alt' =>  $values[$fieldName][0]['alt'],
            'title' => $values[$fieldName][0]['title'],
          ];
        }else{
          $nodeDef[$fieldName] = $values[$fieldName];
        }

      }
    }

    $node = Node::create($nodeDef);
    try {
      $node->field_at_entry[] =  ['target_id' =>  $this->atEntry->id()];
      $node->save();
      $this->atEntry->field_at_descriptions[] =  ['target_id' =>  $node->id()];
      $this->atEntry->save();
    } catch (EntityStorageException $e) {

    }


    $form_state->setRedirect('buddy.at_entry_overview');

    $this->messenger()->addMessage($this->t('The description has been saved as draft! A moderator was informed to approve and publish the description!'));
  }
}

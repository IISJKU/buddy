<?php
namespace Drupal\buddy\Util;

class Util
{
  public static function getFormFieldsOfContentType($contentTypeName,$form, &$form_state){

    //Explanation
    $node = \Drupal\node\Entity\Node::create(['type' => $contentTypeName]);
    $form = \Drupal::service('entity.form_builder')->getForm($node,'default',$form_state->getStorage());

    $entityFieldManager = \Drupal::service('entity_field.manager');
    $definitions  = $entityFieldManager->getFieldDefinitions("node", $contentTypeName);

    $entity_type = 'node';
    $form_mode = 'default';
    $form_display = \Drupal::entityTypeManager()->getStorage('entity_form_display')->load($entity_type .'.' . $contentTypeName . '.' . $form_mode);

    //Set form display to form state
    $form_state->set('form_display', $form_display);
    $fields = [];

    foreach ($form as $fieldName => $element) {


      if (str_starts_with($fieldName, "field_")) {
        if ($widget = $form_display->getRenderer($fieldName)) {

          $items = $node->get($fieldName);
          $items->filterEmptyItems();
          $fields[] = $widget->form($items, $form, $form_state);

        }
      }
    }

    return $fields;
  }

}

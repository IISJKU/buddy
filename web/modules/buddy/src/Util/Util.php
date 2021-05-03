<?php

namespace Drupal\buddy\Util;

use Drupal\Core\Entity\EntityStorageException;

class Util
{
  public static function getFormFieldsOfContentType($contentTypeName, $form, &$form_state, $node = NULL)
  {

    //Explanation
    if (!$node) {
      $node = \Drupal\node\Entity\Node::create(['type' => $contentTypeName]);
    }

    $form = \Drupal::service('entity.form_builder')->getForm($node, 'default', $form_state->getStorage());

    $entityFieldManager = \Drupal::service('entity_field.manager');
    $definitions = $entityFieldManager->getFieldDefinitions("node", $contentTypeName);

    $entity_type = 'node';
    $form_mode = 'default';
    $form_display = \Drupal::entityTypeManager()->getStorage('entity_form_display')->load($entity_type . '.' . $contentTypeName . '.' . $form_mode);

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

  public static function setTitle($title)
  {
    $request = \Drupal::request();
    if ($route = $request->attributes->get(\Symfony\Cmf\Component\Routing\RouteObjectInterface::ROUTE_OBJECT)) {
      $route->setDefault('_title', $title);
    }

  }

  public static function loadNodesByReferences($references){

    $ids = [];
    foreach ($references as $reference){

      $ids[] = $reference['target_id'];

    }
    return \Drupal::entityTypeManager()->getStorage('node')
      ->loadMultiple($ids);

  }

  public static function deleteNodesByReferences($references){
    foreach ($references as $reference){

      $node = \Drupal::entityTypeManager()->getStorage('node')->load($reference['target_id']);
      if($node){
        try {
          $node->delete();
        } catch (EntityStorageException $e) {


        }
      }


    }
  }

}

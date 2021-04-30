<?php
namespace Drupal\buddy\Util;

class Util
{
  public static function getFormFieldsOfContentType($contentTypeName,&$form_state){

    $node = \Drupal\node\Entity\Node::create(['type' => $contentTypeName]);
    $form = \Drupal::service('entity.form_builder')->getForm($node,'default',$form_state->getStorage());

    $entityFieldManager = \Drupal::service('entity_field.manager');
    $fields = $entityFieldManager->getFieldDefinitions("node", $contentTypeName);

    $fields = [];

    foreach ($form as $elementName => $element){


      if(str_starts_with($elementName,"field_")){

        //The original widget cant be used - it is already rendered but for a different form
        //$fields[$elementName] = $element['widget']; does not work
        $oldFormElement = $element['widget'];

        if(isset($oldFormElement[0]['value'])){
          $oldFormElement = $oldFormElement[0]['value'];

        }
        $newFormElement = array();


        self::copyAttributeByKey($newFormElement,$oldFormElement,"#title");
        self::copyAttributeByKey($newFormElement,$oldFormElement,"#type");
        self::copyAttributeByKey($newFormElement,$oldFormElement,"#required");
        self::copyAttributeByKey($newFormElement,$oldFormElement,"#description");
        self::copyAttributeByKey($newFormElement,$oldFormElement,"#weight");
        self::copyAttributeByKey($newFormElement,$oldFormElement,"#default_value");

        switch ($oldFormElement["#type"]){
          case "checkboxes":
          case "radios":   {
            self::copyAttributeByKey($newFormElement,$oldFormElement,"#options");

            break;
          }

          default: {

          }

        }

        $fields[$elementName] = $newFormElement;



        /*
        switch ($element["type"]){
        }
        */
      }

    }

    return $fields;

  }

  private static function copyAttributeByKey(&$copyTarget, $copyOrigin, $key){
    if(array_key_exists("$key",$copyOrigin)){
      $copyTarget[$key] = $copyOrigin[$key];
    }

  }

}

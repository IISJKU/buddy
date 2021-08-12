<?php

namespace Drupal\buddy\Util;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node;

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
          $currentField = $widget->form($items, $form, $form_state);
          $currentField['#weight'] = $element['#weight'];
          $fields[] = $currentField;
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

  public static function getBaseURL(){

    $url_options = [
      'absolute' => TRUE,
      'language' => \Drupal::languageManager()->getCurrentLanguage(),
    ];
    return Url::fromRoute('<front>', [], $url_options)->toString();


  }

  public static function renderDescriptionTabs($description,$shortDescription = false){


    $plainLanguageAvailable = false;
    if($shortDescription){
      if(!empty($description->get("field_at_description_short_plain")->getValue()[0]['value'])){
        $plainLanguageAvailable = true;
      }
    }else{
      if(!empty($description->get("field_at_description_plain_lang")->getValue()[0]['value'])){
        $plainLanguageAvailable = true;
      }
    }

    $header = $shortDescription ? t("Short description") : t("Description");

    $markup = '<nav>
    <div class="nav nav-tabs" role="tablist">
        <a class="nav-link active" id="short_version_tab" data-toggle="tab" href="#short_version_tab_panel" role="tab" aria-controls="extension_tab_panel" aria-selected="true">
            <img src="http://localhost/buddy/web//modules/buddy/img/icons/browser-icon.png" width="50" height="50" alt="" title="">
             '.$header.'
        </a>';
    if($plainLanguageAvailable){
      $headerPlain = $shortDescription ? t("Short description in plain language") : t("Description in plain language");

      $markup.= '<a class="nav-link" id="long_version_tab" data-toggle="tab" href="#long_version_tab_panel" role="tab" aria-controls="extension_tab_panel" aria-selected="false">
            <img src="http://localhost/buddy/web//modules/buddy/img/icons/browser-icon.png" width="50" height="50" alt="" title="">
            '.$headerPlain.'
        </a>';
    }

    $content = $shortDescription ?$description->get("field_at_description_short")->getValue()[0]['value'] : $description->get("field_at_description")->getValue()[0]['value'];

    $markup.= ' </div>
    </nav>
    <div class="tab-content">
              <div class="tab-pane fade show active" id="short_version_tab_panel" role="tabpanel" aria-labelledby="pills-home-tab">
              '.$content.'
            </div>';

    if($plainLanguageAvailable){
      $contentPlain  = $shortDescription ? $description->get("field_at_description_short_plain")->getValue()[0]['value'] : $description->get("field_at_description_plain_lang")->getValue()[0]['value'];
      $markup.= '<div class="tab-pane fade" id="long_version_tab_panel" role="tabpanel" aria-labelledby="pills-profile-tab">
                '.$contentPlain.'</div>';
    }

    $markup.= ' </div>';

    return $markup;


  }

  public static function getDescriptionOfATEntry($atID){

    //Check current language
    $user_lang = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'at_description')
      ->condition('field_at_description_language', $user_lang)
      ->condition('status', 1);
    $results = $query->execute();
    if (!empty($results)) {


      $nid = array_shift($results);

      return Node::load($nid);

    }else{

      //Check user language
      $user = \Drupal::currentUser();
      $account = $user->getAccount();
      $userLang = $account->getPreferredLangcode();

      $query = \Drupal::entityQuery('node')
        ->condition('type', 'at_description')
        ->condition('field_at_description_language', $user_lang)
        ->condition('status', 1);
      $results = $query->execute();
      if (!empty($results)) {


        $nid = array_shift($results);
        return Node::load($nid);

      }else{

        //Check if English version is available
        $userLang = "en";

        $query = \Drupal::entityQuery('node')
          ->condition('type', 'at_description')
          ->condition('field_at_description_language', $user_lang)
          ->condition('status', 1);
        $results = $query->execute();
        if (!empty($results)) {
          $nid = array_shift($results);
          return Node::load($nid);

        }else{

          //Return first language we find ....
          $query = \Drupal::entityQuery('node')
            ->condition('type', 'at_description')
            ->condition('status', 1);
          $results = $query->execute();
          if (!empty($results)) {
            $nid = array_shift($results);
            return Node::load($nid);

          }

        }


      }

    }

  }

}

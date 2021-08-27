<?php

namespace Drupal\buddy\Util;

use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
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

  public static function getBaseURL($useLanguage = true){

    $url_options = [
      'absolute' => TRUE,

    ];
    if($useLanguage){
      $url_options['language'] = \Drupal::languageManager()->getCurrentLanguage();
    }else{
      $url_options['language'] = \Drupal::languageManager()->getDefaultLanguage();
    }
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

    $header = $shortDescription ? t("Information") : t("Description");

    $markup = '<nav>
    <div class="nav nav-tabs" role="tablist">
        <a class="nav-link active" id="short_version_tab" data-toggle="tab" href="#description_tab_panel_'.$description->id().'" role="tab" aria-controls="description_tab_panel_'.$description->id().'" aria-selected="true">
            <img src="'.Util::getBaseURL(false).'/modules/buddy/img/icons/information-icon.png" width="50" height="50" alt="" title="">
             '.$header.'
        </a>';
    if($plainLanguageAvailable){
      $headerPlain = $shortDescription ? t("Information in plain language") : t("Description in plain language");

      $markup.= '<a class="nav-link" id="long_version_tab" data-toggle="tab" href="#plain_description_tab_panel_'.$description->id().'" role="tab" aria-controls="plain_description_tab_panel_'.$description->id().'" aria-selected="false">
            <img src="'.Util::getBaseURL(false).'/modules/buddy/img/icons/plain-language-icon.png" width="50" height="50" alt="" title="">
            '.$headerPlain.'
        </a>';
    }

    $image = $description->field_at_description_at_image->getValue();
    $altText = $image[0]['alt'];
    $styled_image_url = ImageStyle::load('medium')->buildUrl($description->field_at_description_at_image->entity->getFileUri());

    $content = $shortDescription ?$description->get("field_at_description_short")->getValue()[0]['value'] : $description->get("field_at_description")->getValue()[0]['value'];
     $markup.= ' </div>
    </nav>
    <div class="tab-content">
              <div class="tab-pane fade show active" id="description_tab_panel_'.$description->id().'" role="tabpanel" aria-labelledby="pills-home-tab">
              '.Util::renderDescriptionContent($content,$description,$shortDescription).'
    </div>';

    if($plainLanguageAvailable){
      $contentPlain  = $shortDescription ? $description->get("field_at_description_short_plain")->getValue()[0]['value'] : $description->get("field_at_description_plain_lang")->getValue()[0]['value'];
      $markup.= '<div class="tab-pane fade" id="plain_description_tab_panel_'.$description->id().'" role="tabpanel" aria-labelledby="pills-profile-tab">
                 '.Util::renderDescriptionContent($contentPlain,$description,$shortDescription).'
            </div>';
    }

    $markup.= ' </div>';

    return $markup;


  }

  private static function renderDescriptionContent($content, $description,$shortDescription = false){

    if($shortDescription){
      $image = $description->field_at_description_at_image->getValue();
      $altText = $image[0]['alt'];
      $styled_image_url = ImageStyle::load('medium')->buildUrl($description->field_at_description_at_image->entity->getFileUri());

      return '
       <div class="container">
                <div class="row">
                    <div class="col-2">
                        <img src="'.$styled_image_url.'" alt="'.$altText.'" class="img-fluid w-100 at_description_image">
                    </div>
                    <div class="col-10">
                    '.$content.'
                    </div>
                </div>
            </div>';
    }else{

      return $content;
    }

  }

  public static function getDescriptionOfATEntry($atID){

    //Check current language
    $user_lang = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'at_description')
      ->condition('field_at_entry', $atID)
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

  /**
   * Returns a list of weighted user needs
   * @param $user: a fully loaded user account
   * @param bool $finished_only: whether to consider only finished user profiles
   * @return array: an array of user need node ids (keys) and corresponding weights as percentage (values)
   */
  public static function getUserNeeds($user, bool $finished_only=false): array
  {
    $needs_weighted = array();
    if ($user) {
      $query = \Drupal::entityQuery('node')
        ->condition('type', 'user_profile')
        ->condition('uid', $user->id());
      if ($finished_only) {
        $query->condition('field_user_profile_finished', true);
      }
      $results = $query->execute();
      if (!empty($results)) {
        $storage = \Drupal::service('entity_type.manager')->getStorage('node');
        $profile = $storage->load(array_shift($results));
        $user_needs = $profile->get('field_user_profile_user_needs')->getValue();
        foreach ($user_needs as $user_need) {
          $need_entry = $storage->load($user_need['target_id']);
          $category = $need_entry->get('field_user_need_ass_support_cat')->getString();
          $percentage = $need_entry->get('field_user_need_ass_percentage')->getString();
          if ($percentage > 0.009) {
            $needs_weighted[$category] = $percentage;
          }
        }
      }
    }
    return $needs_weighted;
  }

  /**
   * Return a list of all AT entries available in the given language
   * @param $language
   * @param bool $ignorePermissions : TRUE to return all ATs regardless of user access permissions
   * @return array
   */
  public static function listAllATs($language, bool $ignorePermissions=false): array
  {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'at_entry')
      ->condition('field_at_descriptions.entity:node.field_at_description_language', $language)
      ->condition('status', 1)
      ->accessCheck(!$ignorePermissions);
    $results = $query->execute();
    return array_values($results);
  }

  /**
   * Retrieve a list of ATs in the given user's library
   * @param $user : a loaded account
   * @return array: list with AT Entries node ids
   * @throws InvalidPluginDefinitionException
   * @throws PluginNotFoundException
   */
  public static function userLibraryATs($user): array
  {
    $user_ats = array();
    if ($user) {
      $at_records_ids = \Drupal::entityQuery('node')
        ->condition('type', 'user_at_record')
        ->condition('field_user_at_record_library', true)
        ->condition('uid', $user->id())
        ->execute();
      $at_records = \Drupal::entityTypeManager()->getStorage('node')
        ->loadMultiple($at_records_ids);
      foreach ($at_records as $record) {
        $at_entry = $record->get('field_user_at_record_at_entry')->getString();
        $user_ats[] = $at_entry;
      }
    }
    return $user_ats;
  }

}

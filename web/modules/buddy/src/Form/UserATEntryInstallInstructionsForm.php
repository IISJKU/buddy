<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Util\Browser;
use Drupal\buddy\Util\Util;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node;
use http\Url;

class UserATEntryInstallInstructionsForm extends FormBase
{

  protected $browser;
  protected $platform;
  protected $isMobile;



  public function getFormId()
  {
    return "user_entry_detail_form";
  }

  public function buildForm(array $form, FormStateInterface $form_state, $description = null)
  {

    Util::setTitle($this->t("How to get this tool")."");

    $browser = new Browser();
    $this->browser = $browser->getBrowser();
    $this->platform = $browser->getPlatform();
    $this->isMobile = $browser->isMobile();



    //Get AT Entry of description
    $atEntriesID = \Drupal::entityQuery('node')
      ->condition('type', 'at_entry')
      ->condition('field_at_descriptions', $description->id(), '=')
      ->execute();

    $atEntries = \Drupal::entityTypeManager()->getStorage('node')
      ->loadMultiple($atEntriesID);

    $atEntry = array_shift($atEntries);
    $atEntryID = $atEntry->id();
    $descriptions = Util::getDescriptionsOfATEntry($atEntryID);
    $user = \Drupal::currentUser();
    $supportCategories = Util::getSupportCategoriesOfAtEntry(Node::load($atEntryID));
    $description = Util::getDescriptionForUser($descriptions,$user);
    $languages = Util::getLanguagesOfDescriptions($descriptions);
    $platforms = Util::getPlatformsOfATEntry(Node::load($atEntryID));
    $content = Util::renderDescriptionTiles2($description,$supportCategories,$platforms,$languages);

    $form['description'] = [
      '#type' => 'markup',
      '#prefix' => "<div class='at_library_container'>",
      '#markup' => $content,
      '#allowed_tags' => ['button', 'a', 'div', 'img','h3','h2', 'h1', 'p', 'b', 'b', 'strong', 'hr', 'ul', 'li', 'span'],
    ];




    $browserExtensions = [];
    $software = [];
    $apps = [];

    foreach ($platforms as $platform){
      switch ($platform->bundle()){
        case "at_type_browser_extension":{
          $browserExtensions[] = $platform;
          break;
        }

        case "at_type_app": {
          $apps[] = $platform;
          break;
        }

        case "at_type_software": {
          $software[] = $platform;
          break;
        }
        default: {

        }
      }
    }


    $installHTML ="<ul>";

    $installHTML.=$this->renderSoftware($software);
    $installHTML.=$this->renderBrowserExtensions($browserExtensions);
    $installHTML.=$this->renderApps($apps);

    $installHTML.="</ul>";
    $installContent = '
            <div class="row">
                <div class="col-3">
                 </div>
                 <div class="col-9">
                ' .$installHTML . '
                </div>
             </div>';


    $form['introduction_install'] = [
      '#type' => 'markup',
      '#markup' => $installContent,
      '#allowed_tags' => ['button', 'a', 'div','img','ul','li','p','b','strong','hr'],
      '#suffix' => "</div>",

    ];


    $form['#attached']['library'][] = 'buddy/user_at_detail';
    return $form;
  }


  protected function renderBrowserExtensions($extensions){

    $form = [];

    $compatibleExtensions = [];
    $otherExtensions = [];
    foreach ($extensions as $extension){

      $browser = Node::load($extension->get("field_type_browser")->getValue()[0]['target_id']);

      if(strtolower($this->browser) === strtolower($browser->getTitle())){
        $compatibleExtensions[] = ['extension' => $extension, 'browser' => $browser];
      }else{
        $otherExtensions[] = ['extension' => $extension, 'browser' => $browser];

      }


    }


    $html = "";
    foreach ($compatibleExtensions as $currentExtension){

      $downloadLink = $currentExtension['extension']->field_type_download_link->getValue()[0]['uri'];
      $html.=$this->renderDownloadLinkListItem($currentExtension['browser'],$downloadLink,$this->t("Compatible with your browser"));


    }

    foreach ($otherExtensions as $currentExtension){

      $downloadLink = $currentExtension['extension']->field_type_download_link->getValue()[0]['uri'];
      $html.=$this->renderDownloadLinkListItem($currentExtension['browser'],$downloadLink);

    }

    return $html;

  }


  protected function renderSoftware($software){
    $compatibleSoftware = [];
    $otherSoftware = [];
    foreach ($software as $currentSoft){

      $desktopOS = Node::load($currentSoft->get("field_type_software_os")->getValue()[0]['target_id']);
      $osTitle = $desktopOS->getTitle();
      $generalOS = "N/A";
      if(str_contains($osTitle,"win")){
        $generalOS = "windows";
      }else if(str_contains($osTitle,"linux")){
        $generalOS = "linux";
      }else if(str_contains($osTitle,"osx")){
        $generalOS = "osx";
      }

      if(strtolower($this->platform) === strtolower($desktopOS->getTitle())){
        $compatibleSoftware[] = ['software' => $currentSoft, 'os' => $desktopOS];
      }else{

        $otherSoftware[] =  ['software' => $currentSoft, 'os' => $desktopOS];;

      }

    }

    $html="";


    foreach ($compatibleSoftware as $currentSoft){

      $downloadLink = $currentSoft['software']->field_type_download_link->getValue()[0]['uri'];

      $html.=$this->renderDownloadLinkListItem($currentSoft['os'],$downloadLink,$this->t("Compatible with your computer"));
    }
    foreach ($otherSoftware as $currentSoft){
      $downloadLink = $currentSoft['software']->field_type_download_link->getValue()[0]['uri'];

      $html.=$this->renderDownloadLinkListItem($currentSoft['os'],$downloadLink);
    }


    return $html;

  }

  protected function renderApps($apps){

    $compatibleApps = [];
    $otherApps = [];
    foreach ($apps as $application){

    //  $os = $application->get("field_app_operating_system")->getValue()[0]['value'];
      $appOs = Node::load($application->get("field_app_os")->getValue()[0]['target_id']);

      if(strtolower($this->platform) === strtolower($appOs->getTitle())){
        $compatibleApps[] = ['app' => $application, 'os' => $appOs];
      }else{

        $otherApps[] = ['app' => $application, 'os' => $appOs];
      }


    }



    $html = "";

    foreach ($compatibleApps as $currentApp){

      $downloadLink = $currentApp['app']->field_type_download_link->getValue()[0]['uri'];
      $html.=$this->renderDownloadLinkListItem($currentApp['os'],$downloadLink,$this->t("Compatible with your device"));

    }

    foreach ($otherApps as $currentApp){
      $downloadLink = $currentApp['app']->field_type_download_link->getValue()[0]['uri'];
      $html.=$this->renderDownloadLinkListItem($currentApp['os'],$downloadLink);

    }

    return $html;
  }

  protected function renderDownloadLinkListItem($type,$url,$additionalMessage=null){

    if($additionalMessage){
      $additionalMessage = " - ".$additionalMessage;
    }
    return '<li><a href="'.$url.'">'.$this->t("Download for ").$type->getTitle().$additionalMessage.'</a></li>';

  }

  protected function renderTypeDescription($type,$id,$currentMessage){
    $icon = $type->field_icon->getValue();
    $altText = $icon[0]['alt'];
    $styled_image_url = ImageStyle::load('medium')->buildUrl($type->field_icon->entity->getFileUri());
    $description = "";
    if($currentMessage != ""){

      $currentMessage = '('.$currentMessage.')';
    }

    $descriptionID = "buddy_description_id_".$id;
    $description.= "
            <h3 id='".$descriptionID."'><img src='".$styled_image_url."' alt='' class='buddy-type-icon'>".$type->getTitle().$currentMessage."</h3>";


    $button =  [
      '#type' => 'submit',
      '#name' => $id,
      '#button_type' => 'primary',
      '#value' => $this->t('Get it!'),
      '#prefix' =>'<li class="er_type_install_item"> <div class="row"> <div class="col-8 buddy_install_description">'.$description.'</div><div class="col-4">',
      '#suffix' => '</div></div></li>',

    ];

    $button['#attributes']['class'][] = 'buddy_link_button buddy_button buddy_download';
    $button['#attributes']['class'][] = 'buddy-icon-button';
    $button['#attributes']['class'][] = 'buddy-icon-before';
    $button['#attributes']['icon'] = "fa-download";
    $button['#attributes']['aria-describedby'][] = $descriptionID;

    return $button;

  }

  protected function renderBrowserDescription($browser,$id,$currentBrowserMessage =""){
    return $this->renderTypeDescription($browser,$id,$currentBrowserMessage);
  }

  protected function renderOsDescription($os,$id, $currentOSMessage = ""){
    return $this->renderTypeDescription($os,$id, $currentOSMessage);
  }

  protected function renderMobileOSDescription($os,$id, $currentOSMessage = ""){

    return $this->renderTypeDescription($os,$id, $currentOSMessage);
  }








  protected function renderTabHeader($name, $icon, $tabID, $tabPanelID, $activeTab){

    $active = "";
    $ariaSelected = "false";
    if($activeTab){
      $active = "active";
      $ariaSelected = "true";
    }

    return '<a class="nav-link buddy_install_tab_link '.$active.'" id="'.$tabID.'" data-toggle="tab" href="#'.$tabPanelID.'" role="tab" aria-controls="'.$tabPanelID.'" aria-selected="'.$ariaSelected.'"><img src="'.$icon.'" width="50" height="50" alt="" title="">'.$name.'</a>';

  }


  protected function renderTabPanelHeader($tabID, $tabPanelID, $activeTab){

    $activeTabHTML = "";
    if($activeTab){
      $activeTabHTML =" show active";
    }
    return '  <div class="tab-pane fade'.$activeTabHTML.'" id="'.$tabPanelID.'" role="tabpanel" aria-labelledby="'.$tabID.'">';
  }






  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    $typID  = $form_state->getTriggeringElement()['#name'];

    $type = Node::load($typID);

    $link = $type->get("field_type_download_link")->getValue();


    $form_state->setResponse(new TrustedRedirectResponse($link[0]['uri']));

  }

}

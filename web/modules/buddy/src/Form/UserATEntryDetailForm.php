<?php


namespace Drupal\buddy\Form;


use Drupal\buddy\Util\Browser;
use Drupal\buddy\Util\Util;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\node\Entity\Node;

class UserATEntryDetailForm extends FormBase
{

  protected $browser;
  protected $platform;
  protected $isMobile;


  protected $tabHeaderHTML = "";
  protected $tabPanelHTML = "";

  public function getFormId()
  {
    return "user_entry_detail_form";
  }

  public function buildForm(array $form, FormStateInterface $form_state, $description = null)
  {

    $browser = new Browser();
    $this->browser = $browser->getBrowser();
    $this->platform = $browser->getPlatform();
    $this->isMobile = $browser->isMobile();
    $atEntryID = $description->get("field_at_entry")->getValue()[0]['target_id'];

    $atEntry = Node::load($atEntryID);


    $typesIDs = $atEntry->get("field_at_types")->getValue();



    $browserExtensions = [];
    $software = [];
    $apps = [];
    foreach ($typesIDs as $typeID){

      $type = Node::load($typeID['target_id']);

      $platform = $type->bundle();

      switch ($type->bundle()){
        case "at_type_browser_extension":{
          $browserExtensions[] = $type;
          break;
        }

        case "at_type_app": {
          $apps[] = $type;
          break;
        }

        case "at_type_software": {
          $software[] = $type;
          break;
        }
        default: {

        }
      }
    }

    Util::setTitle($description->getTitle());

    $activeTab = true;
    if(count($browserExtensions)){

      $this->renderBrowserExtensions($browserExtensions,$activeTab);

      $activeTab = false;
    }


    if(count($software)){
      $this->renderSoftware($software,$activeTab);
      $activeTab = false;
    }

    if(count($apps)){
      $this->renderApps($apps,$activeTab);
      $activeTab = false;
    }


    $markup = '<nav>
  <div class="nav nav-tabs" id="nav-tab" role="tablist">
    <a class="nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true"><img src="https://orf.at/mojo/1_4_1/storyserver//news/news/images/target_news.svg" width="101" height="39" alt="ORF.at" title="" class="orfon-target-logo">Home</a>
    <a class="nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false"><img src="https://orf.at/mojo/1_4_1/storyserver//news/news/images/target_news.svg" width="101" height="39" alt="ORF.at" title="" class="orfon-target-logo">Profile</a>
    <a class="nav-link" id="nav-contact-tab" data-toggle="tab" href="#nav-contact" role="tab" aria-controls="nav-contact" aria-selected="false"><img src="https://orf.at/mojo/1_4_1/storyserver//news/news/images/target_news.svg" width="101" height="39" alt="ORF.at" title="" class="orfon-target-logo">Contact</a>
  </div>
</nav>
<div class="tab-content" id="nav-tabContent">
  <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">asdf</div>
  <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">asdfffff</div>
  <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">ddfsf</div>
</div>';


    $markup = '<nav>
  <div class="nav nav-tabs" id="nav-tab" role="tablist">
  '.$this->tabHeaderHTML.'  </div>
</nav>
<div class="tab-content" id="nav-tabContent">
'.$this->tabPanelHTML.'
</div>';


    $form['text'] = [
      '#type' => 'markup',
      '#markup' => $markup,
      '#allowed_tags' => ['button', 'a', 'div','img','h2','h1','p','b','b','strong','hr'],

    ];


    $form['text2'] = [
      '#type' => 'markup',
      '#markup' => $_SERVER['HTTP_USER_AGENT'],

    ];
    $form['#attached']['library'][] = 'buddy/user_at_detail';
    return $form;
  }


  protected function renderBrowserExtensions($extensions,$activeTab){
    $sortedExtensions = [];
    foreach ($extensions as $extension){

      $browser = $extension->get("field_type_browser")->getValue()[0]['value'];

      if(strtolower($this->browser) === strtolower($browser)){
        array_unshift($sortedExtensions,[
          'extension' => $extension,
          'actual_browser' => true
          ]);;
      }else{

        $sortedExtensions[] = [
          'extension' => $extension,
          'actual_browser' => false,
        ];

      }


    }

    $this->tabHeaderHTML.= $this->renderTabHeader($this->t("Browser Extension"),Util::getBaseURL()."/modules/buddy/img/icons/browser-icon.png", "extension_tab","extension_tab_panel",$activeTab);

    $extensionHTML = "<h3>".$this->t("This browser extension is available for:")."</h3>";
    foreach ($sortedExtensions as $sortedExtension){

      if($sortedExtension['actual_browser']){

        $extensionHTML.="<h2>Actual Browser:".$sortedExtension['extension']->getTitle();
      }else{
        $extensionHTML.="<h2>Browser:".$sortedExtension['extension']->getTitle();
      }


    }


    $this->tabPanelHTML.= $this->renderTabPanel("extension_tab","extension_tab_panel",$activeTab,$extensionHTML);


  }

  protected function renderApps($apps,$activeTab){

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
    $this->tabHeaderHTML.= $this->renderTabHeader($this->t("Apps"),Util::getBaseURL()."/modules/buddy/img/icons/app-icon.png", "app_tab","app_tab_panel",$activeTab);


    $appHTML = '<h2>'.$this->t("This assistive technology is available for the following operating system(s):").'</h2>';
    foreach ($compatibleApps as $currentApp){

      $icon = $currentApp['os']->field_icon->getValue();
      $altText = $icon[0]['alt'];
      $styled_image_url = ImageStyle::load('medium')->buildUrl($currentApp['os']->field_icon->entity->getFileUri());


      $appHTML.= "<h3>".$currentApp['os']->getTitle()."</h3><p><b>".$this->t('You are currently using this system.')."</b>
                    <br><img src='".$styled_image_url."' alt='".$altText."' class='buddy-type-icon'>".$currentApp['os']->field_description->getValue()[0]['value']."</p>";
      $appHTML.= $this->createDownloadLink("asdf","Download the extension");
    }

    foreach ($otherApps as $currentApp){

      $icon = $currentApp['os']->field_icon->getValue();
      $altText = $icon[0]['alt'];
      $styled_image_url = ImageStyle::load('medium')->buildUrl($currentApp['os']->field_icon->entity->getFileUri());

      $appHTML.="<hr>";
      $appHTML.= "<h3>".$currentApp['os']->getTitle()."</h3><p><img src='".$styled_image_url."' alt='".$altText."' class='buddy-type-icon'>".$currentApp['os']->field_description->getValue()[0]['value']."</p>";
      $appHTML.= $this->createDownloadLink("asdf","Download the extension");
    }


    $this->tabPanelHTML.= $this->renderTabPanel("app_tab","app_tab_panel",$activeTab,$appHTML);
  }

  protected function renderSoftware($software,$activeTab){
    $compatibleSoftware = [];
    $otherSoftware = [];
    foreach ($software as $currentSoft){

      $os = $currentSoft->get("field_type_desk_operating_system")->getValue()[0]['value'];
      $desktopOS = Node::load($currentSoft->get("field_type_software_os")->getValue()[0]['target_id']);

      $generalOS = "N/A";
      if(str_contains($os,"win")){
        $generalOS = "windows";
      }else if(str_contains($os,"linux")){
        $generalOS = "linux";
      }else if(str_contains($os,"osx")){
        $generalOS = "osx";
      }

      if(strtolower($this->platform) === strtolower($desktopOS->getTitle())){
        $compatibleSoftware[] = ['software' => $currentSoft, 'os' => $desktopOS];
      }else{

        $otherSoftware[] =  ['software' => $currentSoft, 'os' => $desktopOS];;

      }


    }

    $this->tabHeaderHTML.= $this->renderTabHeader($this->t("Software"),Util::getBaseURL()."/modules/buddy/img/icons/desktop-icon.png", "software_tab","software_tab_panel",$activeTab);

    $softwareHTML =  '<h2>'.$this->t("This assistive technology is available for the following  desktop operating system(s):").'</h2>';
    foreach ($compatibleSoftware as $currentSoft){

      $icon = $currentSoft['os']->field_icon->getValue();
      $altText = $icon[0]['alt'];
      $styled_image_url = ImageStyle::load('medium')->buildUrl($currentSoft['os']->field_icon->entity->getFileUri());


      $softwareHTML.= "<h3>".$currentSoft['os']->getTitle()."</h3><p><b>".$this->t('You are currently using this system.')."</b>
                    <br><img src='".$styled_image_url."' alt='".$altText."' class='buddy-type-icon'>".$currentSoft['os']->field_description->getValue()[0]['value']."</p>";
      $softwareHTML.= $this->createDownloadLink("asdf","Download the extension");
    }

    foreach ($otherSoftware as $currentSoft){
      $icon = $currentSoft['os']->field_icon->getValue();
      $altText = $icon[0]['alt'];
      $styled_image_url = ImageStyle::load('medium')->buildUrl($currentSoft['os']->field_icon->entity->getFileUri());

      $softwareHTML.="<hr>";
      $softwareHTML.= "<h3>".$currentSoft['os']->getTitle()."</h3><p><img src='".$styled_image_url."' alt='".$altText."' class='buddy-type-icon'>".$currentSoft['os']->field_description->getValue()[0]['value']."</p>";
      $softwareHTML.= $this->createDownloadLink("asdf","Download the extension");
    }


    $this->tabPanelHTML.= $this->renderTabPanel("software_tab","software_tab_panel",$activeTab,$softwareHTML);


  }


  protected function renderTabHeader($name, $icon, $tabID, $tabPanelID, $activeTab){

    $active = "";
    $ariaSelected = "false";
    if($activeTab){
      $active = "active";
      $ariaSelected = "true";
    }

    return '<a class="nav-link '.$active.'" id="'.$tabID.'" data-toggle="tab" href="#'.$tabPanelID.'" role="tab" aria-controls="'.$tabPanelID.'" aria-selected="'.$ariaSelected.'"><img src="'.$icon.'" width="50" height="50" alt="" title="">'.$name.'</a>';

  }

  protected function renderTabPanel($tabID, $tabPanelID, $activeTab, $html){

    $activeTabHTML = "";
    if($activeTab){
      $activeTabHTML =" show active";
    }
    return '  <div class="tab-pane fade'.$activeTabHTML.'" id="'.$tabPanelID.'" role="tabpanel" aria-labelledby="'.$tabID.'">'.$html.'</div>';
  }


  protected function createDownloadLink($url,$linkText = "Download"){

    return '<a href="'.$url.'"><img src="'.Util::getBaseURL()."/modules/buddy/img/icons/download-icon.png".'" alt="" class="buddy-download-icon">'.$linkText.'</a>';
  }




  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    // TODO: Implement submitForm() method.
  }

}

<?php


namespace Drupal\buddy\Util;


class BuddyRecommender
{

  protected static int $maxNumberOfATEntries = 1;

  /**
   * Return AT recommendations for the given user, or the current logged-in user if no user is given
   * @param $user
   */
    public static function recommend($user) {
      if (!$user) {
        $user = \Drupal::currentUser();
      }
      $user_needs = Util::getUserNeeds($user);
      $user_lang = \Drupal::languageManager()->getCurrentLanguage()->getId();
      $ats = Util::listAllATs($user_lang);
      $user_ats = Util::userLibraryATs($user);
      if (!empty($ats)) {

      }
    }
}

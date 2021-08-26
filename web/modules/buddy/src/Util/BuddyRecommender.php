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
      $ats = BuddyRecommender::listAllATs($user_lang);
      if (!empty($ats)) {

      }
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
}

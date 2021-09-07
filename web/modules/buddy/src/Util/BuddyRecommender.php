<?php


namespace Drupal\buddy\Util;


use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;

class BuddyRecommender
{

  public static int $maxNumberOfATEntries = 1;

  /**
   * Return AT recommendations for the given user, or the current logged-in user if no user is given
   * @param $user: a loaded user account. If none, the current logged-in user will be loaded
   * @param array $ignore_ats: list of node ids of AT entries to ignore as recommendations
   * @return array: list of node ids of AT entries (in the user's language) to recommend to the user
   * @throws InvalidPluginDefinitionException
   * @throws PluginNotFoundException
   */
    public static function recommend($user=null, array $ignore_ats=[]): array
    {
      $final_recs = array();
      if (!$user) {
        $user = \Drupal::currentUser();
      }
      $user_lang = \Drupal::languageManager()->getCurrentLanguage()->getId();
      $ats = Util::listAllATs($user_lang);  // TODO: cache this
      if (!empty($ats)) {
        $user_ats = Util::userLibraryATs($user);
        $candidates = array_diff($ats, $user_ats);
        $candidates = array_diff($candidates, $ignore_ats);
        if (!empty($candidates)) {
          $user_needs = Util::getUserNeeds($user);
          foreach ($candidates as $at) {
            $score = BuddyRecommender::knowledge_score($at, $user_needs);
            $recs[$at] = $score;
          }
          arsort($recs);
          $final_recs = array_slice(array_keys($recs), 0, BuddyRecommender::$maxNumberOfATEntries);
        }
      }
      return $final_recs;
    }

  /**
   * Compute the knowledge-based similarity score of a given AT for a given user
   * @param $at_nid: node id of the AT entry
   * @param $user_needs: array with user need categories node ids
   * @return float: similarity score
   */
    protected static function knowledge_score($at_nid, $user_needs) {
      $score = 0.0;
      if (!empty($user_needs) && is_numeric($at_nid) && $at_nid > 0) {
        $at = \Drupal::service('entity_type.manager')->getStorage('node')->load($at_nid);
        $support_array = $at->get('field_at_categories')->getValue();
        $support_cats = array();
        foreach ($support_array as $target) {
          $support_cats[] = $target['target_id'];
        }
        if (!empty($support_cats)) {
          foreach ($user_needs as $cat => $value) {
            if (in_array($cat, $support_cats)) {
              $score += $value/100;
            }
          }
        }
      }
      return $score/count($user_needs);
    }
}

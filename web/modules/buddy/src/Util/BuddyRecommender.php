<?php


namespace Drupal\buddy\Util;


use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

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
        $score = $score/count($user_needs);
      }
      return $score;
    }

    public static function get_ratings_based_recommendation($uid) {

    }

  /**
   * Log in to the Buddy API and return the authorization token
   * @return false|string auth token if login successful; false otherwise
   */
    public static function log_in_buddy_API() {
      $base_url = Util::getBuddyEnvVar('rating_service');
      $route = Util::getBuddyEnvVar('rating_service_login_route');
      $username = Util::getBuddyEnvVar('rating_service_account');
      $pwd = Util::getBuddyEnvVar('rating_service_pwd');
      if (!$route | !$base_url | !$username | !$pwd) {
        return false;
      }
      try {
        $response = \Drupal::httpClient()->post($base_url . $route, [
          'verify' => true,
          'json' => [
            'email' => $username,
            'password' => $pwd
          ],
        ]);
        $code = $response->getStatusCode();
        if ($code >= 200 && $code < 300) {
          $content = $response->getBody()->getContents();
          $payload = json_decode($content, TRUE);
          return $payload['Authorization'];
        } else {
          \Drupal::logger('buddy')->error(
            'logInBuddyAPI error: returned code ' . $code);
          return false;
        }
      } catch (\Exception $e) {
        watchdog_exception('buddy', $e, 'logInBuddyAPI error');
        return false;
      }
    }

  /**
   * Post the ratings that have been cached in the DB to the Buddy Recommender API service.
   * After all ratings have been sent, clear the cache table
   */
    public static function post_recent_ratings() {
      $route = Util::getBuddyEnvVar('rating_service_route');
      $post_base_url = Util::getBuddyEnvVar('rating_service');
      if (!$route | !$post_base_url) {
        return;
      }
      // Select all ratings cached in DB
      $connection = \Drupal::database();
      $query = $connection->select('rating_cache', 'r')->fields('r')->execute();
      $results = $query->fetchAll(\PDO::FETCH_OBJ);
      if (count($results) > 0) {
        $payload = array();
        foreach ($results as $row) {
          $payload[] = array(
            'user_id' => (int) $row->uid,
            'item_id' => (int) $row->at_nid,
            'rating' => (int) $row->rating,
          );
        }
        $auth_token = BuddyRecommender::log_in_buddy_API();
        if ($auth_token === false) {
          return;
        }
        try {
          $response = \Drupal::httpClient()->post($post_base_url . $route, [
            'verify' => true,
            'json' => $payload,
            'headers' => [
                'Authorization' => "Bearer {$auth_token}"
            ]
          ]);
          $code = $response->getStatusCode();
          if ($code >= 200 && $code < 300) {
            $contents = json_decode($response->getBody()->getContents(), TRUE);
            if ($contents['status'] == 'success') {
              \Drupal::logger('buddy')->info($contents['message']);
              // Clean cache
              $connection->delete('rating_cache')->execute();
              \Drupal::logger('buddy')->info('rating_cache cleared.');
            } else {
              \Drupal::logger('buddy')->error(
                'postRecentRatings error: ' . print_r($contents, TRUE));
            }
          } else {
            \Drupal::logger('buddy')->error(
              'postRecentRatings error: returned code ' . $code);
          }
        } catch (ConnectException | ClientException | RequestException $e) {
          watchdog_exception('buddy', $e);
        } catch (\Exception $e) {
          watchdog_exception('buddy', $e, 'postRecentRatings: Unknown exception caught');
        }
      }
    }
}

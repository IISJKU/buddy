<?php


namespace Drupal\buddy\Util;


use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Database\Database;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

class BuddyRecommender
{

  public static int $maxNumberOfATEntries = 1;

  /**
   * Return AT recommendations for the given user, or the current logged-in user if no user is given
   * @param $user : a loaded user account. If none, the current logged-in user will be loaded
   * @param array $ignore_ats : list of node ids of AT entries to ignore as recommendations
   * @return array: list of node ids of AT entries (in the user's language) to recommend to the user
   * @throws InvalidPluginDefinitionException
   * @throws PluginNotFoundException
   */
  public static function recommend($user = null, array $ignore_ats = []): array
  {
    $final_recs = array();
    if (!$user) {
      $user = \Drupal::currentUser();
    }
    $user_ats = Util::userLibraryATs($user);
    // Fetch recommendations from cache
    $recs = BuddyRecommender::get_cached_recommendations($user->id());
    // Cache empty -> compute recommendations
    if (empty($recs)) {
      $user_lang = \Drupal::languageManager()->getCurrentLanguage()->getId();
      $ats = Util::listAllATs($user_lang);
      if (!empty($ats)) {
        $candidates = array_diff($ats, $user_ats);
        $candidates = array_diff($candidates, $ignore_ats);
        // Start by computing knowledge-based scores
        if (!empty($candidates)) {
          $user_needs = Util::getUserNeeds($user);
          foreach ($candidates as $at) {
            $score = BuddyRecommender::knowledge_score($at, $user_needs);
            $recs[$at] = $score;
          }
        }
        // Combine with data-based scores, if available
        $top_k = max(10, min(count($ats), 100));
        $data_recs = BuddyRecommender::get_ratings_based_recommendation($user->id(), $top_k);
        if ($data_recs !== false && !empty($data_recs)) {
          $data_recs_indexed = array();
          foreach ($data_recs as $prediction) {
            if ($prediction['confidence'] > 0.0) {
              $data_recs_indexed[$prediction['item_id']] = array();
              $data_recs_indexed[$prediction['item_id']]['confidence'] = $prediction['confidence'];
              $data_recs_indexed[$prediction['item_id']]['rating'] = $prediction['predicted_rating'];
            }
          }
          foreach($recs as $at_nid => $ks) {
            if (array_key_exists($at_nid, $data_recs_indexed)) {
              $c = $data_recs_indexed[$at_nid]['confidence'];
              $ds = $data_recs_indexed[$at_nid]['rating'];
              $final_recs[$at_nid] = $c*$ds + (1.0-$c)*$ks;
            } else {
              $final_recs[$at_nid] = 0.5*$ks;
            }
          }
        } else {
          $final_recs = $recs;
        }
        // Add new recommendations to cache
        if (!empty($recs)) {
          $connection = \Drupal::database();
          $query = $connection->insert('recs_cache')
            ->fields(['uid', 'at_nid', 'score']);
          foreach ($recs as $i => $s) {
            $query->values([
              $user->id(), $i, $s
            ]);
          }
          try {
            $query->execute();
          } catch (\Exception $e) {
            watchdog_exception('buddy', $e);
          }
        }
      }
    } else {
      $recs = array_diff_key($recs, array_combine($user_ats, $user_ats));
      $final_recs = array_diff_key($recs, array_combine($ignore_ats, $ignore_ats));
    }
    if (!empty($final_recs)) {
      arsort($final_recs);
      $final_recs = array_slice(array_keys($final_recs), 0, BuddyRecommender::$maxNumberOfATEntries);
    }
    return $final_recs;
  }

  /**
   * Fetch cached AT entries recommendations for a target user
   * @param int $uid: target user's UID
   * @return array<int, float>: AT recommendations for the user. Indices are node ids,
   * values are recommendation scores.
   */
  protected static function get_cached_recommendations(int $uid) : array {
    $recs = array();
    try {
      $connection = \Drupal::database();
      $query = $connection->select('recs_cache', 'r')
        ->fields('r')
        ->condition('r.uid', $uid)
        ->execute();
      $results = $query->fetchAll(\PDO::FETCH_OBJ);
      foreach ($results as $row) {
        $recs[(int)$row->at_nid] = (float)$row->score;
      }
    } catch (\Exception $e) {
      watchdog_exception('buddy', $e);
    } finally {
      return $recs;
    }
  }

  /**
   * Compute the knowledge-based similarity score of a given AT for a given user
   * @param $at_nid : node id of the AT entry
   * @param $user_needs : array with user need categories node ids
   * @return float: similarity score
   */
  protected static function knowledge_score($at_nid, $user_needs)
  {
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
            $score += $value / 100;
          }
        }
      }
      $score = $score / count($user_needs);
    }
    return $score;
  }

  /**
   * Get data-based AT entries recommendations from the Buddy Recommender API
   * @param int $uid User id of the target account
   * @param int $top_k number of recommendations to request
   * @return false|mixed array with recommendation (user_id, item_id, predicted_rating, confidence) tuples;
   * false if an error occurred (e.g. user does not exist).
   */
  public static function get_ratings_based_recommendation(int $uid, int $top_k = 1)
  {
    $base_url = Util::getBuddyEnvVar('rating_service');
    $route = Util::getBuddyEnvVar('rating_service_predict_route');
    if (!$route | !$base_url) {
      return false;
    }
    $auth_token = BuddyRecommender::log_in_buddy_API();
    if ($auth_token === false) {
      return false;
    }
    $endpoint = $base_url . $route . '/top/' . $top_k . '/user/' . $uid;
    try {
      $response = \Drupal::httpClient()->get($endpoint, [
        'headers' => [
          'Authorization' => "Bearer {$auth_token}"
        ]
      ]);
      $code = $response->getStatusCode();
      if ($code >= 200 && $code < 300) {
        $contents = json_decode($response->getBody()->getContents(), TRUE);
        return $contents['data'];
      } else {
        \Drupal::logger('buddy')->error(
          'get_ratings_based_recommendation error: returned code ' . $code);
        return false;
      }
    } catch (ConnectException | ClientException | RequestException $e) {
      watchdog_exception('buddy', $e);
    } catch (\Exception $e) {
      watchdog_exception('buddy', $e,
        'get_ratings_based_recommendation: Unknown exception caught');
    }
    return false;
  }

  /**
   * Log in to the Buddy API and return the authorization token
   * @return false|string auth token if login successful; false otherwise
   */
  public static function log_in_buddy_API()
  {
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
  public static function post_recent_ratings()
  {
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
          'user_id' => (int)$row->uid,
          'item_id' => (int)$row->at_nid,
          'rating' => (int)$row->rating,
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

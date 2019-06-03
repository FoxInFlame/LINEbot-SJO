<?php

/**
 * A part of the Setagaya Junior Orchestar LINE Bot Webhook Receiver.
 * 
 * 
 * @since 1.0.0
 * @author Yuto Takano <moa17stock@gmail.com>
 * @version 1.0.0
 */

namespace SJOLine\Helpers;

use GuzzleHttp;
use DOMDocument;
use Exception;

/**
 * Construct a SJO session.
 * 
 * @since 1.0.0
 * @author Yuto Takano <moa17stock@gmail.com>
 */
class SJOAuthenticator
{

  /**
   * Will contain the ManageBac Guzzle cookie jar to pass in requests.
   * @var CookieJar
   */
  public $session;

  /**
   * Will contain the Guzzle client to use for sending requests to ManageBac.
   * @var GuzzleHttp\Client
   */
  public $client;

  /**
   * Constructor function to create a Guzzle client to use later.
   * 
   * @since 1.0.0
   */
  public function __construct() {
    
    $this->client = new GuzzleHttp\Client([
      'base_uri' => 'http://www.s-j-o.jp/'
    ]);

  }

  /**
   * Login, and store the cookies' jar for further requests.
   * 
   * @since 1.0.0
   */
  public function authenticate() {

    $cookieJar = new GuzzleHttp\Cookie\CookieJar;

    $loginResponse = $this->client->request(
      'POST',
      'wp/wp-login.php',
      [
        'cookies' => $cookieJar,
        'allow_redirects' => false,
        'form_params' => [
          'log' => SJO_LOGIN,
          'pwd' => SJO_PASSWORD,
          'rememberme' => 'forever',
          'wp-submit' => 'ログイン',
          'redirect_to' => 'http://www.s-j-o.jp/members'
        ]
      ]
    );

    if($loginResponse->getStatusCode() === 200) {
      throw new Exception('Invalid SJO credentials provided.');
    }

    $final_location = $loginResponse->getHeader('Location')[0];

    if(strpos($final_location, '/members') === false) {
      throw new Exception('Invalid SJO credentials provided.');
    }
    
    $postpassResponse = $this->client->request(
      'POST',
      'wp/wp-login.php?action=postpass',
      [
        'cookies' => $cookieJar,
        'allow_redirects' => false,
        'form_params' => [
          'post_password' => SJO_POST_PASSWORD,
          'Submit' => '確定'
        ]
      ]
    );

    if($postpassResponse->getStatusCode() === 200) {
      throw new Exception('Invalid SJO post credentials provided.');
    }

    $this->session = $cookieJar;

    return true;

  }

}

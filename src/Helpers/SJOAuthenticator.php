<?php

/**
 * A part of the Setagaya Junior Orchestar LINE Bot Webhook Receiver.
 * 
 * 
 * @since 1.0.0
 * @author Yuto Takano <moa17stock@gmail.com>
 * @version 2.0.0
 */

namespace SJOLine\Helpers;

use GuzzleHttp;
use DOMDocument;
use Exception;
use SJOLine\Helpers\ShellExec;
use Sunra\PhpSimple\HtmlDomParser;

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
      'base_uri' => 'https://s-j-o.jp/'
    ]);

  }

  /**
   * Login, and store the cookies' jar for further requests.
   * Throws an exception if there is an error.
   * 
   * @return Boolean Logged in = true
   * @since 1.0.0
   */
  public function authenticate($tries = 0) {

    $cookieJar = new GuzzleHttp\Cookie\CookieJar;

    $pageResponse = $this->client->request(
      'GET',
      'members',
      [
        'cookies' => $cookieJar,
        'allow_redirects' => false
      ]
    );

    $html = HtmlDomParser::str_get_html($pageResponse->getBody());
    
    $nonce = $html->find('#wpmem_login #_wpmem_login_nonce', 0)->value;
    $captcha = $html->find('#wpmem_login img[alt="CAPTCHA"]', 0)->src;
    $captcha_prefix = $html->find('#wpmem_login #siteguard_captcha_prefix', 0)->value;

    (new ShellExec())->execute('/usr/bin/python3 ' . BASE_DIR . '/predict.py ' . escapeshellarg($captcha), null, $out, $out, 30);
    $captcha_result = trim($out);
    $loginResponse = $this->client->request(
      'POST',
      'members',
      [
        'cookies' => $cookieJar,
        'allow_redirects' => true,
        'form_params' => [
          '_wpmem_login_nonce' => $nonce,
          '_wp_http_referer' => '/members',
          'log' => SJO_LOGIN,
          'pwd' => SJO_PASSWORD,
          'siteguard_captcha' => $captcha_result,
          'siteguard_captcha_prefix' => $captcha_prefix,
          'a' => 'login',
          'Submit' => 'ログイン'
        ]
      ]
    );

    // if($loginResponse->getStatusCode() !== 302) {
    //   throw new Exception('Invalid SJO credentials provided.');
    // }

    $html = HtmlDomParser::str_get_html($loginResponse->getBody());
    
    $error = $html->find('#content #wpmem_msg', 0);
    if ($error) {
      // perhaps captcha was wrong, retry 3 times
      if ($tries > 0) {
        throw new Exception('Invalid SJO credentials provided, or CAPTCHA failed 3 times in a row.');
      }
      $this->authenticate($tries + 1); 
      return;
    }

    // captcha passed but still couldn't log in 
    $logged_in = $html->find('#content .logged-in', 0);
    if (!$logged_in) {
      throw new Exception('Invalid SJO credentials provided.');
    }

    $this->session = $cookieJar;

    return true;

  }

}

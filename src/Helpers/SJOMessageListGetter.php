<?php

/**
 * A part of the Setagaya Junior Orchestra LINE Bot Webhook Receiver.
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
use Sunra\PhpSimple\HtmlDomParser;

/**
 * Grab the list of messages using the session obtained in SJOAuthenticator.
 * 
 * @since 1.0.0
 * @author Yuto Takano <moa17stock@gmail.com>
 */
class SJOMessageListGetter
{

  /**
   * Will contain the SJO Guzzle cookie jar to pass in requests.
   * @var CookieJar
   */
  private $session;

  /**
   * Will contain the Guzzle client to use for sending requests to SJO.
   * @var GuzzleHttp\Client
   */
  private $client;

  /**
   * Will contain the message IDs.
   * @var Array
   */
  private $messages = [];

  /**
   * Fill in the required fields
   * 
   * @param CookieJar $session Cookie Jar for Guzzle requests
   * @param GuzzleHttp\Client $client Client to always use for this session
   * @since 1.0.0
   */
  public function __construct($session, $client) {

    $this->session = $session;
    $this->client = $client;

  }

  /**
   * Get messages ids as an array.
   * 
   * @return Array
   * @since 1.0.0
   */
  public function getMessages() {
  
    $this->getMessageList('/member_news');

    return $this->messages;

  }

  /**
   * Get the list of message IDs on the first page. 
   * Do not overload the target server, and increase runtime by recursing all pages.
   * This script is ran every 15/30 minutes anyway so there will never be over 12 items in this span (on the new layout).
   * 
   * @param String $url The base URL fragment for the message list
   */
  private function getMessageList($url) {

    $response = $this->client->request(
      'GET',
      $url . '/page/1',
      [
        'cookies' => $this->session
      ]
    );

    $html = HtmlDomParser::str_get_html($response->getBody());

    $messages = $html->find('#content > .archive > section > .list article');
    
    foreach ($messages as $index => $message) {
      $link_parts = explode('/', $message->find('a', 0)->href);
      array_push($this->messages, substr(end($link_parts), 0, -5));
    }

  }

}

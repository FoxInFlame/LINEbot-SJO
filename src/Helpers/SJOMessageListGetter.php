<?php

/**
 * A part of the Setagaya Junior Orchestra LINE Bot Webhook Receiver.
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
   * @since 1.0.0
   */
  public function getMessages() {

    $first_message_link = $this->getFirstMessage('/members');
    $this->getMessageList($first_message_link);

    return $this->messages;

  }

  /**
   * Get the first message so that we can load the list of messages in the sidebar
   * 
   * @param String $url The base relative URL for the messages
   * @since 1.0.0
   */
  private function getFirstMessage($url) {

    $response = $this->client->request(
      'GET',
      $url,
      [
        'cookies' => $this->session
      ]
    );  

    $html = HtmlDomParser::str_get_html($response->getBody());

    $link = $html->find('#contents #main .post a', 0)->href;

    // Free RAM and collect garbage
    $html->clear();
    gc_collect_cycles();

    if(!$link) return;

    return $link;

  }

  private function getMessageList($url) {

    $response = $this->client->request(
      'GET',
      $url,
      [
        'cookies' => $this->session
      ]
    );

    $html = HtmlDomParser::str_get_html($response->getBody());

    $messages = $html->find('#sidebar ul li');
    
    foreach ($messages as $index => $message) {
      if ($index > 5) break;
      $link_parts = explode('/', $message->find('a', 0)->href);
      array_push($this->messages, end($link_parts));
    }

  }

}

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
 * Grab the HTML of messages using the session obtained in SJOAuthenticator.
 * 
 * @since 1.0.0
 * @author Yuto Takano <moa17stock@gmail.com>
 */
class SJOMessageGetter
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
   * Get message information as an array.
   * 
   * @since 1.0.0
   */
  public function getMessage($id) {

    $response = $this->client->request(
      'GET',
      '/archives/' . $id,
      [
        'cookies' => $this->session
      ]
    );

    $html = HtmlDomParser::str_get_html($response->getBody());

    $post_info = $html->find('#contents #main', 0);
    $title = $post_info->find('h1.title text', 0)->innertext;
    $date = $post_info->find('.post-info ul li text', 0)->innertext;
    $paragraphs = $post_info->find('p');
    $content = array_map(function($item) {
      return implode('', array_map(function($text) {
        return $text->innertext;
      }, $item->find('text')));
    }, $paragraphs);

    return [
      'title' => $title,
      'content' => implode('<br><br>', $content),
      'date' => $date
    ];

  }

}

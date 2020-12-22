<?php

/**
 * A part of the Setagaya Junior Orchestra LINE Bot Webhook Receiver.
 * 
 * @since 1.0.0
 * @author Yuto Takano <moa17stock@gmail.com>
 * @version 2.0.0
 */

namespace SJOLine\Helpers;

use GuzzleHttp;
use \Exception;

/**
 * Core file for sending requests to LINE's API.
 * 
 * @since 1.0.0
 * @author Yuto Takano <moa17stock@gmail.com>
 */
class LINERequest
{

  /**
   * Contains the request HTTP method.
   * @var String
   */
  private $method;

  /**
   * Contains the full URL to the API call.
   * @var String
   */
  private $url;

  /**
   * Contains the array POST data.
   * @var Array
   */
  private $post_data;

  /**
   * Prepare the sending.
   * 
   * @param String $method HTTP Method for sending to the API
   * @param String $url The relative URL to the API
   * @param Array $post_data The POST data encoded into a string
   * @since 1.0.0
   */
  public function prepare($method, $url, $post_data) {
    $this->method = $method;
    $this->url = $url;
    $this->post_data = $post_data;
  }

  /**
   * Send the prepared request.
   * 
   * @since 1.0.0
   */
  public function send() {

    if(!$this->url) {
      return false;
    }
    $client = new GuzzleHttp\Client([
      'base_uri' => 'https://api.line.me/v2/bot/'
    ]);
    $send_data = [
      'headers' => [
        'Authorization' => 'Bearer ' . ACCESS_TOKEN,
        'Content-Type' => 'application/json'
      ]
    ];
    if($this->method === 'POST') {
      $send_data['json'] = $this->post_data;
    }
    try {
      $response = $client->request(
        $this->method,
        $this->url,
        $send_data
      );

    } catch (GuzzleHttp\Exception\ClientException $e) { // 400 level errors
      $response = $e->getResponse();
      $responseBodyAsString = $response->getBody()->getContents();
      echo $response->getStatusCode() . ' ' . $responseBodyAsString.PHP_EOL;
      echo 'Sent JSON:' . PHP_EOL.json_encode($send_data['json'], JSON_PRETTY_PRINT) . '</pre>';
    } catch (GuzzleHttp\Exception\ServerException $e) { // 500 level errors
      $response = $e->getResponse();
      $responseBodyAsString = $response->getBody()->getContents();
      echo $response->getStatusCode() . ' ' . $responseBodyAsString;
    }
  }

}
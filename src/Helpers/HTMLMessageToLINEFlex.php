<?php

/**
 * A part of the Setagaya Junior Orchestra LINE Bot Webhook Receiver.
 * 
 * @since 1.0.0
 * @author Yuto Takano <moa17stock@gmail.com>
 * @version 1.0.0
 */

namespace SJOLine\Helpers;

error_reporting(E_ALL);
ini_set('display_errors', 1);

use \Exception;
use \DOMDocument;
use Sunra\PhpSimple\HtmlDomParser;

/**
 * Converts HTML ManageBac Messages (where new lines are already <br>s) into suitable assoc. array format
 * 
 * @since 1.0.0
 * @author Yuto Takano <moa17stock@gmail.com>
 */
class HTMLMessageToLINEFlex
{

  /**
   * Takes all the parameters as strings and mashes them up into one array that is basically
   * the final result sent to LINE. 
   * 
   * The reason why strings are accepted individually instead of passing the entire array is to
   * ensure the helper function can be used easily from other places when required sometime later.
   * 
   * @param String $id The SJO message ID extracted from HTML
   * @param String $title The title of the message
   * @param String $body The message body, with paragraph breaks replaced by two <br>s.
   * @param String $date The date string (YYYY年MM月DD日)
   * @return Array
   * @since 1.0.0
   */
  public static function convert($id, $title, $body, $date) {

    $altText_str = self::generateAltText($id, $title, $body);

    $date_arr = self::convertDate($date);

    $title_arr = self::convertTitle($title);

    $body_arr = self::convertBody($body);

    $button_arr = self::createButton($id);
    
    $id_arr = self::convertID($id);

    $final_arr = self::createFinalArray($altText_str, $date_arr, $title_arr, $body_arr, $button_arr, $id_arr);

    return $final_arr;

  }

  /**
   * Generate the alt text for old devices and PC. Also shown in the main chat menu.
   * 
   * @param String $id The SJO post ID
   * @param String $title Title of the message
   * @param String $body HTML Body of the message
   * @return String
   * @since 1.0.0
   */
  private static function generateAltText($id, $title, $body) {

    $uri = 'http://www.s-j-o.jp/archives/' . $id;

    // Alt text has a max of 400
    return '♬' . substr(htmlspecialchars_decode($title, ENT_QUOTES) . "\n\n古いLINEバージョン、又はパソコンをお使いの方はこちらから：" . $uri, 0, 390);

  }

  /**
   * Convert the date into its own array. 
   * 
   * @param String $date The date string (YYYY年MM月DD日)
   * @return Array
   * @since 1.0.0
   */
  private static function convertDate($date) {

    return [
      'type' => 'text',
      'text' => $date,
      'color' => '#1DB446',
      'size' => 'xxs'
    ];

  }

  /**
   * Convert the title into its own array. Simple.
   * 
   * @param String $title Title of the message
   * @return Array
   * @since 1.0.0
   */
  private static function convertTitle($title) {

    return [
      'type' => 'text',
      'text' => htmlspecialchars_decode($title, ENT_QUOTES),
      'weight' => 'bold',
      'size' => 'md',
      'margin' => 'md',
      'wrap' => true
    ];

  }

  /**
   * Convert the body HTML (links removed, newlines as <br>) to final array format.
   * 
   * @param String $body Body HTML
   * @return Array
   * @since 1.0.0
   */
  private static function convertBody($body) {

    $body_paragraphs = preg_split('/(<br>|<br\/>|<br \/>)/', $body);

    // Final array that will be returned
    $body_paragraphs_arr = [];

    foreach($body_paragraphs as $key => $paragraph) {

      if($key > 14) {
        $body_paragraphs_arr[] = [
          'type' => 'text',
          'text' => '.....字数制限により端折っていますので、続きはサイトで読みましょう',
          'wrap' => true,
          'size' => 'xxs',
          'color' => '#ce7a16',
          'margin' => 'lg'
        ];
        break;
      }

      $paragraph = str_replace("&nbsp;", '', $paragraph);

      if(trim($paragraph) === '') continue;
      $paragraph_arr = [
        'type' => 'text',
        'text' => html_entity_decode($paragraph),
        'wrap' => true,
        'size' => 'xs',
        'margin' => 'none'
      ];
      
      // Add margin if the previous paragraph was empty. First check if it's not first paragraph.
      if($key > 0) {
        // Then check if previous paragraph was empty
        if($body_paragraphs[$key - 1] === '') {
          $paragraph_arr['margin'] = 'lg';
        }
      }

      $body_paragraphs_arr[] = $paragraph_arr;

    }

    return $body_paragraphs_arr;

  }

  /**
   * Create the "サイトで見る" button.
   * 
   * @param String $id SJO ID of the message
   * @return Array
   * @since 1.0.0
   */
  private static function createButton($id) {

    $uri = 'http://www.s-j-o.jp/archives/' . $id;

    return [
      'type' => 'button',
      'action' => [
        'type' => 'uri',
        'label' => 'サイトで読む',
        'uri' => $uri
      ],
      'style' => 'primary',
      'color' => '#0cc0e7',
      'margin' => 'lg',
      'height' => 'sm'
    ];

  }

  /**
   * Convert ID to its own array. Dead simple.
   * 
   * @param String $id SJO ID of the message
   * @return Array
   * @since 1.0.0
   */
  private static function convertID($id) {

    return [
      'type' => 'box',
      'layout' => 'horizontal',
      'margin' => 'md',
      'contents' => [
        [
          'type' => 'text',
          'text' => '投稿 ID',
          'size' => 'xs',
          'color' => '#aaaaaa',
          'flex' => 0
        ], [
          'type' => 'text',
          'text' => '#' . $id,
          'color' => '#aaaaaa',
          'size' => 'xs',
          'align' => 'end'
        ]
      ]
    ];

  }

  private static function createFinalArray(
    $altText_str, $date_arr, $title_arr, $body_arr, $button_arr, $id_arr) {

      return [
        'type' => 'flex',
        'altText' => $altText_str,
        'contents' => [
          'type' => 'bubble',
          'styles' => [
            'footer' => [
              'separator' => true
            ]
          ],
          'body' => [
            'type' => 'box',
            'layout' => 'vertical',
            'contents' => [
              $date_arr,
              $title_arr,
              [
                'type' => 'separator',
                'margin' => 'xxl'
              ],
              [
                'type' => 'box',
                'layout' => 'vertical',
                'margin' => 'xxl',
                'contents' => $body_arr
              ],
              $button_arr,
              [
                'type' => 'separator',
                'margin' => 'xxl'
              ],
              $id_arr
            ]
          ] 
        ]
      ];

  }

}
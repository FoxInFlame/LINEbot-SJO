<?php

/**
 * A part of the Setagaya Junior Orchestra LINE Bot Webhook Receiver.
 * 
 * @since 1.0.0
 * @author Yuto Takano <moa17stock@gmail.com>
 * @version 1.0.0
 */

namespace SJOLine\Receivers;

use SJOLine\Helpers\LINERequest;
use SJOLine\Helpers\HTMLMessageToLINEFlex;
use \Exception;

/**
 * Check if there are new message and if yes send them to the group chat
 * 
 * @since 1.0.0
 * @author Yuto Takano <moa17stock@gmail.com>
 */
class SJOMessageReceiver
{

  /**
   * Constructor and basic handler.
   * 
   * @param Array $current_message_ids The array of IDs currently posted.
   * @since 1.0.0
   */
  public function __construct($current_message_ids) {

    // Create cache folder if not exist
    if(!file_exists(__DIR__ . '/../../app/cache')) {
      mkdir(__DIR__ . '/../../app/cache', 0777, true);
    }

    if(!file_exists(__DIR__ . '/../../app/cache/Messages.json')) {
      
      $previous_message_ids = [];
    
    } else { 

      $previous_message_ids = @json_decode(
        file_get_contents(__DIR__ . '/../../app/cache/IBMessages.json')
      , true) ?? [];

    }

    file_put_contents(__DIR__ . '/../../app/cache/IBMessages.json', json_encode($current_message_ids));

    $new_message_ids = array_diff($current_message_ids, $previous_message_ids);

    // Reverse the order so that posting them becomes chronological
    $new_message_ids = array_reverse($new_message_ids);

    // Loop for every message before every recipient
    foreach($new_message_ids as $new_message_id) {

      $message_html_data = (new SJOMessageGetter())->getMessage($new_message_id);

      $message_data = HTMLMessageToLINEFlex::convert(
        $new_message_id,
        $message_html_data['title'],
        $message_html_data['body'],
        $message_html_data['date']
      );
      
      $request = new LINERequest();
      $request->prepare('POST', 'message/push', [
        'to' => 'U5be75f0130106ee42c8fb194c302f7b9',
        'messages' => [
          $message_data
        ]
      ]);
      
      $request->send();

    }

  }

}
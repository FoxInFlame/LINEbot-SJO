<?php

/**
 * A part of the Setagaya Junior Orchestra LINE Bot Webhook Receiver.
 * 
 * @since 1.0.0
 * @author Yuto Takano <moa17stock@gmail.com>
 * @version 1.0.0
 */

namespace SJOLine;

use SJOLine\Helpers\SJOAuthenticator;
use SJOLine\Helpers\SJOMessageListGetter;
use SJOLine\Receivers\SJOMessageReceiver;

/**
 * App core class for SJOLine.
 * 
 * @since 1.0.0
 * @author Yuto Takano <moa17stock@gmail.com>
 */
class SJOLine
{

  /**
   * Check for new SJO messages - called by cronjob.
   * 
   * @since 1.0.0
   */
  public function checkMessages() {

    $sjo = new SJOAuthenticator();
    $sjo->authenticate();
    
    // Construct, and use the session we created before.
    $message_getter = new SJOMessageListGetter(
      $sjo->session,
      $sjo->client
    );

    // Use the stored tokens to get the messages
    $messages_ids = $message_getter->getMessages();

    // Initiate a receiver, which checks if there are new messages, and sends the new ones
    $receiver = new SJOMessageReceiver($sjo->session, $sjo->client, $messages_ids);

  }

}
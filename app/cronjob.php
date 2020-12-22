<?php

/**
 * A part of the Setagaya Junior Orchestra LINE Bot Webhook Receiver.
 * 
 * @since 1.0.0
 * @author Yuto Takano <moa17stock@gmail.com>
 * @version 2.0.0
 */

require __DIR__ . '/../vendor/autoload.php';

use SJOLine\Builders\RequestBuilder;
use SJOLine\SJOLine;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start();

// Start core.
$SJOLine = new SJOLine();
$SJOLine->checkMessages();

$log = ob_get_clean();

// Don't write in log if there was no output
if (!empty(trim($log))) {
  $log .= PHP_EOL . "----------------------------" . PHP_EOL . PHP_EOL;
  $logFile = "logs/log_" . date("Y.m.d") . ".txt";
  file_put_contents($logFile, $log, FILE_APPEND);
  chmod($logFile, 0777);
}

http_response_code(200);
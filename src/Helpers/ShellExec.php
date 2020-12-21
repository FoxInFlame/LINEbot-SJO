<?php

/**
 * A part of the Setagaya Junior Orchestra LINE Bot Webhook Receiver.
 * 
 * @since 1.0.0
 * @author Yuto Takano <moa17stock@gmail.com>
 * @version 1.0.0
 */

namespace SJOLine\Helpers;

use GuzzleHttp;
use \Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Class to manage shell command executions. 
 * Courtesy of Vladislav Ross (2012) on StackOverflow.
 * https://stackoverflow.com/questions/3407939/shell-exec-timeout-management-exec
 * 
 * @since 1.0.0
 * @author Yuto Takano <moa17stock@gmail.com>
 */
class ShellExec
{
  function execute($cmd, $stdin=null, &$stdout, &$stderr, $timeout=false) {
    $pipes = array();
    $process = proc_open(
      $cmd,
      array(array('pipe','r'),array('pipe','w'),array('pipe','w')),
      $pipes
    );
    $start = time();
    $stdout = '';
    $stderr = '';

    if(is_resource($process))
    {
      stream_set_blocking($pipes[0], 0);
      stream_set_blocking($pipes[1], 0);
      stream_set_blocking($pipes[2], 0);
      fwrite($pipes[0], $stdin);
      fclose($pipes[0]);
    }

    while(is_resource($process))
    {
      //echo ".";
      $stdout .= stream_get_contents($pipes[1]);
      $stderr .= stream_get_contents($pipes[2]);

      if($timeout !== false && time() - $start > $timeout)
      {
        proc_terminate($process, 9);
        return 1;
      }

      $status = proc_get_status($process);
      if(!$status['running'])
      {
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
        return $status['exitcode'];
      }

      usleep(100000);
    }

    return 1;
  }
}
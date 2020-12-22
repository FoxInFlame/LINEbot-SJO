<?php

/**
 * A part of the Setagaya Junior Orchestra LINE Bot Webhook Receiver.
 * 
 * @since 2.0.0
 * @author Yuto Takano <moa17stock@gmail.com>
 * @version 2.0.0
 */

namespace SJOLine\Helpers;

use GuzzleHttp;
use \Exception;

/**
 * Class to manage shell command executions. 
 * Courtesy of Vladislav Ross (2012) on StackOverflow.
 * https://stackoverflow.com/questions/3407939/shell-exec-timeout-management-exec
 * 
 * @since 2.0.0
 * @author Yuto Takano <moa17stock@gmail.com>
 */
class ShellExec
{

  /**
   * Execute a shell command with a timeout, capturing the output and error.
   * 
   * @param String $cmd Command to execute.
   * @param String $stdin Input
   * @param Pointer $stdout Variable to bind output to
   * @param Pointer $stderr Variable to bind error to
   * @param Integer $timeout Timeout in seconds (I think)
   * @return Integer Exit code (1 = Success)
   * @since 2.0.0
   */
  function execute($cmd, $stdin=null, &$stdout, &$stderr, $timeout = false) {

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
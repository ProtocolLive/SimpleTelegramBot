<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/FuncoesComuns
//2022.04.30.00

function HandlerError(
  int $errno,
  string $errstr,
  string $errfile = null,
  int $errline = null,
  array $errcontext = null
):void{
  DebugTrace();
  ob_start();
  echo '</select><pre>';
  echo 'Error #' . $errno . ' - ' . $errstr . ' in ' . $errfile . ' (' . $errline . ")\n";
  echo "Backtrace:\n";
  debug_print_backtrace();
  echo '</pre>';
  if(ini_get('display_errors')):
    error_log(ob_get_contents());
    ob_end_flush();
    die();
  endif;
  error_log(ob_get_contents());
  ob_end_clean();
}

function HandlerException($Exception):void{
  DebugTrace();
  ob_start();
  echo '</select><pre>';
  echo "Exception:\n";
  var_dump($Exception);
  echo "Backtrace:\n";
  debug_print_backtrace();
  echo '</pre>';
  if(ini_get('display_errors')):
    error_log(ob_get_contents());
    ob_end_flush();
    die();
  endif;
  error_log(ob_get_contents());
  ob_end_clean();
}

function vd(mixed $v):void{
  ob_start();
  echo '</select><pre>';
  echo date('H:i:s') . " Variable debug:\n";
  var_dump($v);
  echo "Backtrace:\n";
  debug_print_backtrace();
  echo '</pre>';
  error_log(ob_get_contents());
  ob_end_flush();
}

function vdd(mixed $v):never{
  vd($v);
  die();
}

$DebugTraceFolder = __DIR__;
function DebugTrace():void{
  global $DebugTraceFolder;
  static $DebugTraceCount = 0;
  $debug = defined('Debug') ? Debug : -1;
  if(($debug & StbDebug::Trace) !== StbDebug::Trace):
    return;
  endif;
  $trace = debug_backtrace();
  $temp = '#' . $DebugTraceCount++ . ' ';
  $temp .= date('Y-m-d H:i:s ') . microtime(true) . "\n";
  $temp .= 'Memory: ' . number_format(memory_get_usage()) . ' ';
  $temp .= 'Limit: ' . ini_get('memory_limit') . ' ';
  $temp .= 'Peak: ' . number_format(memory_get_peak_usage()) . "\n";
  $temp .= $trace[1]['function'];
  $temp .= ' in ' . ($trace[1]['file'] ?? 'unknown');
  $temp .= ' line ' . ($trace[1]['line'] ?? 'unknown') . "\n";
  if(count($trace[1]['args']) > 0):
    ob_start();
    var_dump($trace[1]['args']);
    $temp .= ob_get_contents() . "\n";
    ob_end_clean();
  endif;
  $temp .= "\n";
  file_put_contents($DebugTraceFolder . '/trace.log', $temp, FILE_APPEND);
}
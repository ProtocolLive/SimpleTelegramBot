<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/FuncoesComuns
//2022.08.25.00

function HandlerError(
  int $errno,
  string $errstr,
  string $errfile = null,
  int $errline = null,
  array $errcontext = null
):never{
  DebugTrace();
  $log = PHP_EOL . 'Error: ' . $errstr . PHP_EOL;
  $log .= $errfile . ' (' . $errline . ')' . PHP_EOL;
  ob_start();
  debug_print_backtrace();
  $log .= ob_get_contents();
  ob_end_clean();
  if(ini_get('display_erros')):
    echo '<pre>' . $log;
  endif;
  error_log($log);
  die();
}

function HandlerException(Throwable $Exception):never{
  DebugTrace();
  $log = 'Exception ' . get_class($Exception) . ': ' . $Exception->getMessage() . PHP_EOL;
  $log .= $Exception->getFile() . ' (' . $Exception->getLine() . ')' . PHP_EOL;
  $log .= $Exception->getTraceAsString();
  if(ini_get('display_errors')):
    echo '<pre>' . $log;
  endif;
  error_log($log);
  die();
}

function vd(mixed $v):void{
  ob_start();
  echo '</select><pre>';
  echo date('H:i:s') . ' Variable debug:' . PHP_EOL;
  var_dump($v);
  echo 'Backtrace:' . PHP_EOL;
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
  $temp .= date('Y-m-d H:i:s ') . microtime(true) . PHP_EOL;
  $temp .= 'Memory: ' . number_format(memory_get_usage()) . ' ';
  $temp .= 'Limit: ' . ini_get('memory_limit') . ' ';
  $temp .= 'Peak: ' . number_format(memory_get_peak_usage()) . PHP_EOL;
  $temp .= $trace[1]['function'];
  $temp .= ' in ' . ($trace[1]['file'] ?? 'unknown');
  $temp .= ' line ' . ($trace[1]['line'] ?? 'unknown') . PHP_EOL;
  if(count($trace[1]['args'] ?? []) > 0):
    ob_start();
    var_dump($trace[1]['args']);
    $temp .= ob_get_contents() . PHP_EOL;
    ob_end_clean();
  endif;
  $temp .= PHP_EOL;
  file_put_contents($DebugTraceFolder . '/trace.log', $temp, FILE_APPEND);
}
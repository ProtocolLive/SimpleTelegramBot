<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.03.07.00

require(__DIR__ . '/php.php');
define('DirSystem', dirname(__DIR__, 1));
require(DirSystem . '/system/functions/debug.php');
set_error_handler('HandlerError');
set_exception_handler('HandlerException');
require(__DIR__ . '/constants.php');
require(__DIR__ . '/requires.php');
date_default_timezone_set(Timezone);

$temp = explode(':', Token);
define('DirToken', DirSystem . '/' . $temp[1]);

ini_set('error_log', DirToken . '/logs/error.log');
$DebugTraceFolder = DirToken . '/logs';

$BotData = new TblData(
  Token,
  DirToken . '/logs',
  Debug
);
$Bot = new TelegramBotLibrary($BotData);

//Load modules
$System['Modules'] = [];
foreach(glob(DirSystem . '/modules/*', GLOB_ONLYDIR) as $file):
  $System['Modules'][] = basename($file);
  include($file . '/index.php');
endforeach;
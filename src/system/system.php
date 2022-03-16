<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.03.16.00

require(__DIR__ . '/php.php');
require(__DIR__ . '/requires.php');
set_error_handler('HandlerError');
set_exception_handler('HandlerException');
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
$Db = new StbSysDatabase(DirToken);
$Lang = new StbLanguage(DefaultLanguage);

//Load modules
foreach($Db->Modules() as $module => $install):
  require(DirSystem . '/modules/' . $module . '/index.php');
endforeach;
<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.04.29.02

require(__DIR__ . '/php.php');
require(__DIR__ . '/requires.php');
set_error_handler('HandlerError');
set_exception_handler('HandlerException');
date_default_timezone_set(Timezone);

ini_set('error_log', DirToken . '/logs/error.log');
$DebugTraceFolder = DirToken . '/logs';

$BotData = new TblData(
  Token,
  DirToken . '/logs',
  Debug,
  TestServer
);
$Bot = new TelegramBotLibrary($BotData, TestServer);
$Db = new StbSysDatabase(DirToken);
$Lang = new StbLanguageSys(DefaultLanguage);
$UserLang = DefaultLanguage;

//Load modules
foreach($Db->Modules() as $module => $install):
  require(DirToken . '/modules/' . $module . '/index.php');
endforeach;
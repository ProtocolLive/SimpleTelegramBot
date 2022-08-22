<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.08.22.01

require(__DIR__ . '/php.php');
require(__DIR__ . '/requires.php');
require(DirToken . '/config.php');

use ProtocolLive\TelegramBotLibrary\TblObjects\TblData;
use ProtocolLive\TelegramBotLibrary\StbObjects\{StbDatabase, StbLanguageSys};

set_error_handler('HandlerError');
set_exception_handler('HandlerException');
date_default_timezone_set(Timezone);

ini_set('error_log', DirLogs . '/error.log');
$DebugTraceFolder = DirLogs;

$BotData = new TblData(
  Token,
  DirLogs,
  TokenWebhook,
  Debug,
  TestServer
);
$Bot = new TelegramBotLibrary($BotData, TestServer);
$PlDb = new PhpLiveDb(DirToken . '/db.db', Driver: PhpLiveDbDrivers::SqLite);
$Db = new StbDatabase($PlDb);
$Lang = new StbLanguageSys(DefaultLanguage);
$UserLang = DefaultLanguage;
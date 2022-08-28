<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.08.28.01

use ProtocolLive\SimpleTelegramBot\StbObjects\{StbDatabase, StbLanguageSys};
use ProtocolLive\TelegramBotLibrary\TblObjects\TblData;
use ProtocolLive\TelegramBotLibrary\TelegramBotLibrary;

require(__DIR__ . '/php.php');
require(__DIR__ . '/requires.php');
set_error_handler('HandlerError');
set_exception_handler('HandlerException');

spl_autoload_register(function (string $Class){
  $Class = str_replace(
    'ProtocolLive\TelegramBotLibrary',
    DirSystem . '/vendor/protocollive/telegrambotlibrary/src',
    $Class
  );
  $Class = str_replace(
    'ProtocolLive\SimpleTelegramBot\StbObjects',
    DirSystem . '/system/StbObjects',
    $Class
  );
  $Class = str_replace('\\', '/', $Class);
  require($Class . '.php');
});

require(DirToken . '/config.php');
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
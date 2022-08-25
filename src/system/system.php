<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.08.25.00

require(__DIR__ . '/php.php');
require(__DIR__ . '/requires.php');

if(is_file(DirSystem . '/vendor/autoload.php')):
  require(DirSystem . '/vendor/autoload.php');
else:
  spl_autoload_register(function (string $class){
    $class = str_replace('ProtocolLive\TelegramBotLibrary', DirSystem . '/vendor/protocollive/telegrambotlibrary/src', $class);
    $class = str_replace('\\', '/', $class);
    require($class . '.php');
  });
endif;

require(DirToken . '/config.php');

use ProtocolLive\TelegramBotLibrary\TelegramBotLibrary;
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
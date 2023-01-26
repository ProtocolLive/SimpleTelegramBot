<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2023.01.26.01

use ProtocolLive\PhpLiveDb\{
  Drivers,
  PhpLiveDb
};
use ProtocolLive\SimpleTelegramBot\StbObjects\{
  StbDatabase,
  StbLanguageSys
};
use ProtocolLive\TelegramBotLibrary\TblObjects\TblData;
use ProtocolLive\TelegramBotLibrary\TelegramBotLibrary;

require(__DIR__ . '/php.php');
require(dirname(__DIR__) . '/vendor/autoload.php');
set_error_handler('HandlerError');
set_exception_handler('HandlerException');
require(DirBot . '/config.php');
date_default_timezone_set(Timezone);

ini_set('error_log', DirLogs . '/error.log');
$DebugTraceFolder = DirLogs;

$BotData = new TblData(
  Token,
  DirLogs,
  TokenWebhook,
  Log,
  TestServer
);
$Bot = new TelegramBotLibrary($BotData, TestServer);
if(DbType === Drivers::MySql):
  $PlDb = new PhpLiveDb(DbHost, DbUser, DbPwd, DbName);
else:
  $PlDb = new PhpLiveDb(DirBot . '/db.db', Driver: Drivers::SqLite);
endif;
$Db = new StbDatabase($PlDb);
$Lang = new StbLanguageSys(DefaultLanguage);
$UserLang = DefaultLanguage;
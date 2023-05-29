<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2023.05.29.00

use ProtocolLive\PhpLiveDb\{
  Drivers,
  PhpLiveDb
};
use ProtocolLive\SimpleTelegramBot\StbObjects\{
  StbDatabase,
  StbLanguageSys,
  StbLog
};
use ProtocolLive\TelegramBotLibrary\TblObjects\TblData;
use ProtocolLive\TelegramBotLibrary\TelegramBotLibrary;

require(__DIR__ . '/php.php');
require(dirname(__DIR__) . '/vendor/autoload.php');
set_error_handler(HandlerError(...));
set_exception_handler(HandlerException(...));
require(DirBot . '/config.php');
date_default_timezone_set(Timezone);

ini_set('error_log', DirLogs . '/error.log');
$DebugTraceFolder = DirLogs;
const DebugTrace = Log & StbLog::Trace;

$BotData = new TblData(
  Token,
  DirLogs,
  TokenWebhook,
  Log,
  TestServer,
  StbBotTools::TblLog(...)
);
$Bot = new TelegramBotLibrary($BotData);
if(DbType === Drivers::MySql):
  $PlDb = new PhpLiveDb(DbHost, DbUser, DbPwd, DbName);
else:
  $PlDb = new PhpLiveDb(DirBot . '/db.db', Driver: Drivers::SqLite);
endif;
$Db = new StbDatabase($PlDb);
$Lang = new StbLanguageSys(DefaultLanguage);
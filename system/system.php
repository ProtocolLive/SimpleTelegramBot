<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2024.02.23.00

use ProtocolLive\PhpLiveDb\Enums\{
  Drivers
};
use ProtocolLive\PhpLiveDb\PhpLiveDb;
use ProtocolLive\SimpleTelegramBot\StbObjects\{
  StbBotTools,
  StbDatabase,
  StbLanguageSys,
  StbLog
};
use ProtocolLive\TelegramBotLibrary\TblObjects\TblData;
use ProtocolLive\TelegramBotLibrary\TelegramBotLibrary;

require(__DIR__ . '/php.php');
require(dirname(__DIR__) . '/vendor/autoload.php');
set_error_handler(Handler(...));
set_exception_handler(Handler(...));
require(__DIR__ . '/constants.php');
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
  //StbBotTools::TblLog(...)
);
$Bot = new TelegramBotLibrary($BotData);

if(DbType === Drivers::MySql):
  $PlDb = new PhpLiveDb(DbHost, DbUser, DbPwd, DbName);
else:
  $PlDb = new PhpLiveDb(DirBot . '/db.db', Driver: Drivers::SqLite);
endif;

$Db = new StbDatabase($PlDb);

$Lang = new StbLanguageSys(DefaultLanguage);
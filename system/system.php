<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2025.06.29.01

use ProtocolLive\PhpLiveDb\Enums\Drivers;
use ProtocolLive\PhpLiveDb\PhpLiveDb;
use ProtocolLive\SimpleTelegramBot\StbEnums\StbLog;
use ProtocolLive\SimpleTelegramBot\StbObjects\{
  StbCore,
  StbDatabase,
  StbLanguageSys
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
define('DebugTrace', in_array(StbLog::Trace, Log));

$BotData = new TblData(
  Token,
  DirLogs,
  TokenWebhook,
  Log,
  TestServer,
  StbCore::Log(...)
);
$Bot = new TelegramBotLibrary($BotData);

if(DbType === Drivers::MySql):
  $PlDb = new PhpLiveDb(DbHost, DbUser, DbPwd, DbName);
else:
  $PlDb = new PhpLiveDb(DirBot . '/db.db', Driver: Drivers::SqLite);
endif;

$Db = new StbDatabase($PlDb);

$Lang = new StbLanguageSys(DefaultLanguage);
<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.08.31.00

use ProtocolLive\PhpLiveDb\{
  PhpLiveDb, Drivers
};
use ProtocolLive\SimpleTelegramBot\StbObjects\{StbDatabase, StbLanguageSys};
use ProtocolLive\TelegramBotLibrary\TblObjects\TblData;
use ProtocolLive\TelegramBotLibrary\TelegramBotLibrary;

require(__DIR__ . '/php.php');
require(__DIR__ . '/requires.php');
set_error_handler('HandlerError');
set_exception_handler('HandlerException');

require(DirSystem . '/vendor/autoload.php');
spl_autoload_register(function(string $Class){
  if(strpos($Class, 'ProtocolLive\SimpleTelegramBot\StbObjects') === 0):
    $Class = str_replace(
      'ProtocolLive\SimpleTelegramBot\StbObjects',
      DirSystem . '/system/StbObjects',
      $Class
    );
    $Class = str_replace('\\', '/', $Class);
    require($Class . '.php');
  endif;
});

require(DirToken . '/config.php');
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
  $PlDb = new PhpLiveDb(DirToken . '/db.db', Driver: Drivers::SqLite);
endif;
$Db = new StbDatabase($PlDb);
$Lang = new StbLanguageSys(DefaultLanguage);
$UserLang = DefaultLanguage;
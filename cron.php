<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2023.02.02.00

use ProtocolLive\SimpleTelegramBot\StbObjects\{
  StbBotTools,
  StbLog,
  StbModuleTools
};

StbModuleTools::StbModuleLoad($_SERVER['Cron']);
call_user_func($_SERVER['Cron'] . '::Cron');
StbBotTools::StbLog(
  StbLog::Cron,
  'Time: ' . (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'])
);
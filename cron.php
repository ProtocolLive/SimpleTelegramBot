<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2023.02.02.01

use ProtocolLive\SimpleTelegramBot\StbObjects\{
  StbBotTools,
  StbLog,
  StbModuleTools
};

StbModuleTools::Load($_SERVER['Cron']);
call_user_func($_SERVER['Cron'] . '::Cron');
StbBotTools::Log(
  StbLog::Cron,
  'Time: ' . (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'])
);
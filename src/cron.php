<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.08.28.00

use ProtocolLive\SimpleTelegramBot\StbObjects\StbLog;

StbModuleLoad($_SERVER['Cron']);
call_user_func($_SERVER['Cron'] . '::Cron');
StbLog(StbLog::Cron, 'Time: ' . (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']));
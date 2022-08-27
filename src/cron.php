<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.07.31.00

StbModuleLoad($_SERVER['Cron']);
call_user_func($_SERVER['Cron'] . '::Cron');
LogCron('Cron time: ' . (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']));
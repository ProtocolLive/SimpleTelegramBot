<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.06.20.00

StbModuleLoad($_SERVER['Cron']);
call_user_func($_SERVER['Cron'] . '::Cron');
LogBot('Cron time: ' . (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']));
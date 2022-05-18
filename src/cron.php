<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.05.18.00

$start = microtime(true);
StbModuleLoad($_SERVER['Cron']);
call_user_func($_SERVER['Cron'] . '::Cron');
LogBot('Cron time: ' . (microtime(true) - $start));
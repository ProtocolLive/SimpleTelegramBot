<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.04.26.00

require(__DIR__ . '/system/system.php');

ArgV();
$start = microtime(true);
call_user_func($_SERVER['Cron']);
LogBot('Cron time: ' . (microtime(true) - $start));
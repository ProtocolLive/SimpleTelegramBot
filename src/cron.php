<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.03.21.00

require(__DIR__ . '/system/system.php');

ArgV();
$start = microtime(true);
$_SERVER['Cron']();
LogBot('Cron time: ' . (microtime(true) - $start));
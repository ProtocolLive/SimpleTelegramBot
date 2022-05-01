<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.05.01.00

$start = microtime(true);
call_user_func($_SERVER['Cron']);
LogBot('Cron time: ' . (microtime(true) - $start));
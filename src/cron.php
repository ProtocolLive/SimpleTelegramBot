<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.04.27.00

require(__DIR__ . '/system/system.php');

ArgV();
if(isset($_SERVER['Cron'])):
  $start = microtime(true);
  call_user_func($_SERVER['Cron']);
  LogBot('Cron time: ' . (microtime(true) - $start));
endif;
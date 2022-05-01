<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.05.01.00

define('DirToken', __DIR__);
require(dirname(__DIR__, 1) . '/system/system.php');

ArgV();
if(isset($_SERVER['Cron'])):
  require(DirSystem . '/cron.php');
else:
  require(DirSystem . '/entry.php');
endif;
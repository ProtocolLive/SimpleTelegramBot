<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.07.30.00

const DirToken = __DIR__;
require(dirname(__DIR__, 1) . '/system/system.php');

ArgV();
if(isset($_SERVER['Cron'])):
  require(DirSystem . '/cron.php');
else:
  require(DirSystem . '/entry.php');
endif;
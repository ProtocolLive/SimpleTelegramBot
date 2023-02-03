<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2023.02.02.00

use ProtocolLive\SimpleTelegramBot\StbObjects\StbBotTools;

const DirBot = __DIR__;
require(dirname(__DIR__, 1) . '/system/system.php');

ArgV();
$_GET['a'] ??= '';
if(isset($_SERVER['Cron'])):
  StbBotTools::Cron();
elseif(method_exists(StbBotTools::class, 'Action_' . $_GET['a'])):
  call_user_func(StbBotTools::class . '::Action_' . $_GET['a']);
endif;
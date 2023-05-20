<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2023.05.19.00

use ProtocolLive\SimpleTelegramBot\Install\Install;

require(__DIR__ . '/system/php.php');
const DirBot = '';
require(__DIR__ . '/vendor/autoload.php');

if(isset($_GET['step'])):
  Install::Step2();
else:
  Install::Step1();
endif;
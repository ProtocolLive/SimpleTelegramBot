<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.08.27.01

require(__DIR__ . '/system/php.php');
require(__DIR__ . '/system/PhpLiveDb/index.php');
require(__DIR__ . '/system/StbObjects/StbDbAdminPerm.php');
set_error_handler('error');
set_exception_handler('error');
session_start();

$step = filter_input(INPUT_GET, 'step', FILTER_VALIDATE_INT);
if(basename(__FILE__) !== 'index.php'):
  echo 'Protocol SimpleTelegramBot already installed!';
elseif($step === false or $step === null):
  require(__DIR__ . '/install/step1.php');
else:
  require(__DIR__ . '/install/step' . $step . '.php');
endif;

function error():never{
  echo '<p>⚠️ Install error!</p>';
  echo '<pre>';
  var_dump(func_get_args());
  die();
}
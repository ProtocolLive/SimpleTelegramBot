<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2023.01.27.00

require(__DIR__ . '/system/php.php');
const DirBot = '';
require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/system/StbObjects/StbDbAdminPerm.php');
set_error_handler('error');
set_exception_handler('error');
session_start();

$step = filter_input(INPUT_GET, 'step', FILTER_VALIDATE_INT);
if(basename(__FILE__) !== 'index.php'):
  echo 'Protocol SimpleTelegramBot already installed!';
elseif(empty($step)):
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

function CopyRecursive(string $From, string $To):void{
  foreach(glob($From . '/*') as $file):
    if(is_dir($file)):
      mkdir($To . '/' . basename($file), 0755, true);
      CopyRecursive($file, $To . '/' . basename($file));
    else:
      copy($file, $To . '/' . basename($file));
    endif;
  endforeach;
}
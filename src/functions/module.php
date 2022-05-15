<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.05.15.00

function StbModuleLoad(string $Method):bool{
  if(strpos($Method, '::') === false):
    return false;
  endif;
  $module = explode('::', $Method);
  if(count($module) === 1):
    return false;
  endif;
  require(DirModules . '/' . $module[0] . '/index.php');
  return true;
}
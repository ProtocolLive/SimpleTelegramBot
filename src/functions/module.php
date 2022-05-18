<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.05.17.00

function StbModuleSystem():array{
  return ['Admin', 'Modules'];
}

function StbModuleLoad(string $Module):void{
  if(in_array($Module, StbModuleSystem()) === false):
    require(DirModules . '/' . $Module . '/index.php');
  endif;
}
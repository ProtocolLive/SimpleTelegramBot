<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.06.08.00

function StbModuleSystem():array{
  return ['StbAdmin', 'StbModules'];
}

function StbModuleLoad(string $Module):void{
  if(in_array($Module, StbModuleSystem()) === false):
    require(DirModules . '/' . $Module . '/index.php');
  endif;
}
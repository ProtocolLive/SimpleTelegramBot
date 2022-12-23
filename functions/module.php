<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.12.23.00

use ProtocolLive\SimpleTelegramBot\StbObjects\{
  StbAdmin,
  StbAdminModules
};

function StbModuleSystem():array{
  return [StbAdmin::class, StbAdminModules::class];
}

function StbModuleLoad(string $Module):void{
  if(in_array($Module, StbModuleSystem()) === false):
    require(DirModules . '/' . basename($Module) . '/index.php');
  endif;
}
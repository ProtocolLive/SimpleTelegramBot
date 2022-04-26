<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.04.26.00

interface InterfaceModule{
  static public function Install(
    TelegramBotLibrary $Bot,
    TgCallback $Webhook,
    StbSysDatabase $Db,
    StbLanguage $Lang
  );

  static public function Uninstall(
    TelegramBotLibrary $Bot,
    TgCallback $Webhook,
    StbSysDatabase $Db,
    StbLanguage $Lang
  );
}

abstract class StbModuleTools{
  static public function CommandDel(
    array $Commands,
    array|string $Command
  ):array{
    if(is_string($Command)):
      $Command = [$Command];
    endif;
    foreach($Command as $cmd):
      $index = array_search($cmd, array_column($Commands, 'command'));
      unset($Commands[$index]);
    endforeach;
    return $Commands;
  }
}
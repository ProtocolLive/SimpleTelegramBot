<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.04.30.00

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
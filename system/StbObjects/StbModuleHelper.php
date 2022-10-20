<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.10.08.00

namespace ProtocolLive\SimpleTelegramBot\StbObjects;

class StbModuleHelper{
  protected static function ModTable(string $Table):string{
    $temp = debug_backtrace();
    $temp = $temp[1]['class'];
    return 'module_' . $temp . '_' . $Table;
  }
}
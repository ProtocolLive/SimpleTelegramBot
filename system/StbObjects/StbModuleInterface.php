<?php
//2023.01.02.00

namespace ProtocolLive\SimpleTelegramBot\StbObjects;

interface StbModuleInterface{
  public static function Install():void;
  public static function Uninstall():void;
}
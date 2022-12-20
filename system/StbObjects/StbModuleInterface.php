<?php
//2022.12.20.01

namespace ProtocolLive\SimpleTelegramBot\StbObjects;
use ProtocolLive\TelegramBotLibrary\{
  TelegramBotLibrary,
  TgObjects\TgCallback
};

interface StbModuleInterface{
  public static function Install(
    TelegramBotLibrary $Bot,
    TgCallback $Webhook,
    StbDatabase $Db
  ):void;

  public static function Uninstall(
    TelegramBotLibrary $Bot,
    TgCallback $Webhook,
    StbDatabase $Db
  );
}
<?php
//2022.12.20.00

namespace ProtocolLive\SimpleTelegramBot\StbObjects;
use ProtocolLive\TelegramBotLibrary\{
  TelegramBotLibrary,
  TgObjects\TgCallback
};

interface StbModuleInterface{
  public static function Install(
    StbDatabase $Db,
    TgCallback $Webhook,
    TelegramBotLibrary $Bot
  ):void;
}
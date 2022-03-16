<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.03.16.00

interface InterfaceModule{
  static public function Install(
    TelegramBotLibrary $Bot,
    TgCallback $Webhook,
    StbSysDatabase $Db,
    StbLanguage $Lang
  );
}
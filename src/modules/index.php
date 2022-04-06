<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.03.21.00

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
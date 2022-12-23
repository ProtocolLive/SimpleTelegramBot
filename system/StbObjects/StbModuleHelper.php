<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.12.23.00

namespace ProtocolLive\SimpleTelegramBot\StbObjects;
use PDO;
use ProtocolLive\TelegramBotLibrary\{
  TelegramBotLibrary,
  TgObjects\TgCallback
};

abstract class StbModuleHelper{
  /**
   * Run this after the 'create table' block
   */
  protected static function InstallHelper(
    TgCallback $Webhook,
    StbDatabase $Db,
    StbLanguageSys $Lang,
    TelegramBotLibrary $Bot,
    PDO $Pdo,
    array $Commands
  ):void{
    $Pdo->beginTransaction();

    if($Db->ModuleInstall(self::ModName()) === false):
      self::MsgError($Pdo, $Webhook, $Bot, $Lang);
      error_log('Fail to install module ' . self::ModName());
      return;
    endif;

    $cmds = $Bot->MyCmdGet();
    foreach($Commands as $cmd):
      $cmds->Add($cmd[0], $cmd[1]);
      if($Db->CommandAdd($cmd[0], self::ModName()) === false):
        self::MsgError($Pdo, $Webhook, $Bot, $Lang);
        error_log('Fail to add the command ' . $cmd[0]);
        return;
      endif;
    endforeach;
    if($Bot->MyCmdSet($cmds) === null):
      self::MsgError($Pdo, $Webhook, $Bot, $Lang);
      error_log('Fail to add the commands');
      return;
    endif;

    $Bot->TextEdit(
      $Webhook->User->Id,
      $Webhook->Message->Id,
      sprintf($Lang->Get('InstallOk', Group: 'Module'))
    );
    $Pdo->commit();
  }

  private static function ModName():string{
    $temp = debug_backtrace();
    return $temp[2]['class'];
  }

  protected static function ModTable(
    string $Table
  ):string{
    return 'module_' . self::ModName() . '_' . $Table;
  }

  private static function MsgError(
    PDO $Pdo,
    TgCallback $Webhook,
    TelegramBotLibrary $Bot,
    StbLanguageSys $Lang
  ):void{
    $Pdo->Rollback();
    $Bot->TextEdit(
      $Webhook->User->Id,
      $Webhook->Message->Id,
      sprintf($Lang->Get('Fail', Group: 'Module'))
    );
  }

  /**
   * Run this before the 'drop table' block
   */
  protected static function UninstallHelper(
    TgCallback $Webhook,
    StbDatabase $Db,
    StbLanguageSys $Lang,
    TelegramBotLibrary $Bot,
    PDO $Pdo,
    array $Commands
  ):void{
    $Pdo->beginTransaction();

    $Db->ModuleUninstall(self::ModName());

    $cmds = $Bot->MyCmdGet();
    foreach($Commands as $cmd):
      $cmds->Del($cmd[0]);
    endforeach;
    if($Bot->MyCmdSet($cmds) === null):
      self::MsgError($Pdo, $Webhook, $Bot, $Lang);
      return;
    endif;

    $Pdo->commit();
  }
}
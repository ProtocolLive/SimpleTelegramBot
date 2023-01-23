<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2023.01.23.00

namespace ProtocolLive\SimpleTelegramBot\StbObjects;
use ProtocolLive\TelegramBotLibrary\{
  TblObjects\TblMarkupInline,
  TelegramBotLibrary,
  TgObjects\TgCallback
};

class StbAdminModules{
  public static function Callback_Modules():void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var StbDatabase $Db
     * @var StbLanguageSys $Lang
     * @var TgCallback $Webhook
     */
    global $Bot, $Db, $Lang, $Webhook;
    DebugTrace();
    if(AdminCheck($Webhook->User->Id, StbDbAdminPerm::Modules) === null):
      $Bot->CallbackAnswer(
        $Webhook->Id,
        $Lang->Get('Denied', Group: 'Errors')
      );
      return;
    endif;

    $mods = $Db->Modules();
    $mk = new TblMarkupInline;
    $line = 0;
    $col = 0;
    $mk->ButtonCallback(
      $line,
      $col++,
      $Lang->Get('Back'),
      $Db->CallBackHashSet(StbAdmin::Callback_AdminMenu(...))
    );
    $mk->ButtonCallback(
      $line,
      $col++,
      $Lang->Get('Add'),
      $Db->CallBackHashSet(self::Callback_ModuleAdd(...))
    );
    $line = 1;
    $col = 0;
    foreach($mods as $mod):
      if($Db->ModuleRestricted($mod['module'])):
        continue;
      endif;
      $mk->ButtonCallback(
        $line,
        $col++,
        basename($mod['module']),
        $Db->CallBackHashSet(self::Callback_Mod(...), $mod['module'])
      );
      if($col === 4):
        $col = 0;
        $line++;
      endif;
    endforeach;

    $Bot->TextEdit(
      $Webhook->Data->Data->Chat->Id,
      $Webhook->Data->Data->Id,
      $Lang->Get('Modules', Group: 'Module'),
      Markup: $mk
    );
  }

  public static function Callback_ModuleAdd():void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var StbDatabase $Db
     * @var StbLanguageSys $Lang
     * @var TgCallback $Webhook
     */
    global $Bot, $Db, $Lang, $Webhook;
    DebugTrace();
    if(AdminCheck($Webhook->User->Id, StbDbAdminPerm::Modules) === null):
      $Bot->CallbackAnswer(
        $Webhook->Id,
        $Lang->Get('Denied', Group: 'Errors')
      );
      return;
    endif;

    $mk = new TblMarkupInline;
    $line = 0;
    $col = 0;

    $modules = [];
    foreach(glob(DirModules . '/*', GLOB_ONLYDIR) as $file):
      $modules[] = basename($file);
    endforeach;
    $modules = array_diff($modules, array_column($Db->Modules(), 'module'));

    $mk->ButtonCallback(
      $line,
      $col++,
      $Lang->Get('Back'),
      $Db->CallBackHashSet(self::Callback_Modules(...))
    );
    foreach($modules as $mod):
      $mk->ButtonCallback(
        $line,
        $col++,
        $mod,
        $Db->CallBackHashSet(self::Callback_InsModPic(...), $mod)
      );
      if($col === 4):
        $line++;
        $col = 0;
      endif;
    endforeach;

    $Bot->TextEdit(
      $Webhook->Data->Data->Chat->Id,
      $Webhook->Data->Data->Id,
      $Lang->Get('InstallPick', Group: 'Module'),
      Markup: $mk
    );
  }

  public static function Callback_InsModPic(string $Module):void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var TgCallback $Webhook
     * @var StbLanguageSys $Lang
     * @var StbDatabase $Db
     */
    global $Bot, $Webhook, $Lang, $Db;
    DebugTrace();
    if(AdminCheck($Webhook->User->Id, StbDbAdminPerm::Modules) === null):
      $Bot->CallbackAnswer(
        $Webhook->Id,
        $Lang->Get('Denied', Group: 'Errors')
      );
      return;
    endif;

    $mk = new TblMarkupInline;
    $line = 0;
    $col = 0;

    require(DirModules . '/' . $Module . '/index.php');
    if(method_exists($Module, 'Install') === false):
      $mk->ButtonCallback(
        $line,
        $col++,
        $Lang->Get('Back'),
        $Db->CallBackHashSet(self::Callback_ModuleAdd(...))
      );
      $Bot->TextEdit(
        $Webhook->Data->Data->Chat->Id,
        $Webhook->Data->Data->Id,
        $Lang->Get('InstallNotFound', null, 'Module'),
        Markup: $mk
      );
      return;
    endif;
    if(method_exists($Module, 'Uninstall') === false):
      $mk->ButtonCallback(
        $line,
        $col++,
        $Lang->Get('Back'),
        $Db->CallBackHashSet(self::Callback_ModuleAdd(...))
      );
      $Bot->TextEdit(
        $Webhook->Data->Data->Chat->Id,
        $Webhook->Data->Data->Id,
        $Lang->Get('UninstallNotFound', null, 'Module'),
        Markup: $mk
      );
      return;
    endif;
    call_user_func($Module . '::Install', $Bot, $Webhook, $Db, $Lang);
  }

  public static function Callback_Mod(
    string $Module
  ):void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var StbDatabase $Db
     * @var StbLanguageSys $Lang
     * @var TgCallback $Webhook
     */
    global $Bot, $Db, $Lang, $Webhook;
    DebugTrace();
    if(AdminCheck($Webhook->User->Id, StbDbAdminPerm::Modules) === null):
      $Bot->CallbackAnswer(
        $Webhook->Id,
        $Lang->Get('Denied', Group: 'Errors')
      );
      return;
    endif;

    $mk = new TblMarkupInline;
    $line = 0;
    $col = 0;
    $mk->ButtonCallback(
      $line,
      $col++,
      $Lang->Get('Back'),
      $Db->CallBackHashSet(self::Callback_Modules(...))
    );
    $mk->ButtonCallback(
      $line,
      $col++,
      $Lang->Get('UninstallButton', Group: 'Module'),
      $Db->CallBackHashSet(self::Callback_UniModPic1(...), $Module)
    );
    $date = $Db->Modules($Module);
    $Bot->TextEdit(
      Admin,
      $Webhook->Data->Data->Id,
      sprintf(
        $Lang->Get('Module', Group: 'Module'),
        $Module,
        date(
          $Lang->Get('DateTime'),
          $date[0]['created']
        )
      ),
      Markup: $mk
    );
  }

  public static function Callback_UniModPic1(string $Module):void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var StbLanguageSys $Lang
     * @var TgCallback $Webhook
     * @var StbDatabase $Db
     */
    global $Bot, $Lang, $Webhook, $Db;
    DebugTrace();
    if(AdminCheck($Webhook->User->Id, StbDbAdminPerm::Modules) === null):
      $Bot->CallbackAnswer(
        $Webhook->Id,
        $Lang->Get('Denied', Group: 'Errors')
      );
      return;
    endif;

    $mk = new TblMarkupInline;
    $mk->ButtonCallback(
      0,
      0,
      $Lang->Get('Back'),
      $Db->CallBackHashSet(self::Callback_Mod(...), $Module)
    );
    $mk->ButtonCallback(
      0,
      1,
      $Lang->Get('Yes'),
      $Db->CallBackHashSet(self::Callback_UniModPic2(...), $Module)
    );
    $Bot->MarkupEdit(
      Admin,
      $Webhook->Data->Data->Id,
      Markup: $mk
    );
  }

  public static function Callback_UniModPic2(string $Module):void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var TgCallback $Webhook
     * @var StbDatabase $Db
     * @var StbLanguageSys $Lang
     */
    global $Bot, $Webhook, $Db, $Lang;
    DebugTrace();
    if(AdminCheck($Webhook->User->Id, StbDbAdminPerm::Modules) === null):
      $Bot->CallbackAnswer(
        $Webhook->Id,
        $Lang->Get('Denied', Group: 'Errors')
      );
      return;
    endif;
    require(DirModules . '/' . $Module . '/index.php');
    call_user_func($Module . '::Uninstall', $Bot, $Webhook, $Db, $Lang);
  }
}
<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.06.18.01

class StbModules{
  static public function Callback_Modules():void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var StbDatabaseSys $Db
     * @var StbLanguageSys $Lang
     * @var TblCmd $Webhook
     */
    global $Bot, $Db, $Lang, $Webhook;
    DebugTrace();
    if($Webhook->User->Id !== Admin):
      $Bot->TextSend(
        $Webhook->User->Id,
        $Lang->Get('Denied')
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
      $Db->CallBackHashSet('Admin::Callback_AdminMenu();')
    );
    $mk->ButtonCallback(
      $line,
      $col++,
      $Lang->Get('Add'),
      $Db->CallBackHashSet(get_class() . '::Callback_ModuleAdd();')
    );
    $line = 1;
    $col = 0;
    foreach($mods as $mod => $time):
      $mk->ButtonCallback(
        $line,
        $col++,
        $mod,
        $Db->CallBackHashSet("Callback_Mod('$mod');")
      );
      if($col === 4):
        $col = 0;
        $line++;
      endif;
    endforeach;

    $Bot->TextEdit(
      Admin,
      $Webhook->Message->Id,
      $Lang->Get('Modules', Group: 'Module'),
      Markup: $mk
    );
  }

  static public function Callback_ModuleAdd():void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var StbDatabaseSys $Db
     * @var StbLanguageSys $Lang
     * @var TgCallback $Webhook
     */
    global $Bot, $Db, $Lang, $Webhook;
    DebugTrace();
    $admin = $Db->Admin($Webhook->User->Id);
    if(($admin->Perms & StbDbAdminPerm::Modules->value) === false):
      $Bot->TextSend(
        $Webhook->User->Id,
        $Lang->Get('Denied')
      );
      return;
    endif;

    $mk = new TblMarkupInline;
    $line = 0;
    $col = 0;

    $ModulesFiles = [];
    $sysmods = StbModuleSystem();
    foreach(glob(DirModules . '/*', GLOB_ONLYDIR) as $file):
      $file = basename($file);
      if(in_array($file, $sysmods) === false):
        $ModulesFiles[] = $file;
      endif;
    endforeach;
    foreach($Db->Modules() as $mod => $time):
      $temp = array_search($mod, $ModulesFiles);
      if($temp !== null):
        unset($ModulesFiles[$temp]);
      endif;
    endforeach;

    $mk->ButtonCallback(
      $line,
      $col++,
      $Lang->Get('Back'),
      $Db->CallBackHashSet(get_class() . '::Callback_Modules();')
    );
    foreach($ModulesFiles as $mod):
      $mk->ButtonCallback(
        $line,
        $col++,
        $mod,
        $Db->CallBackHashSet("Callback_InsModPic('$mod');")
      );
      if($col === 4):
        $line++;
        $col = 0;
      endif;
    endforeach;

    $Bot->TextEdit(
      Admin,
      $Webhook->Message->Id,
      $Lang->Get('InstallPick', Group: 'Module'),
      Markup: $mk
    );
  }

  static public function Callback_InsModPic(string $Module):void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var TgCallback $Webhook
     * @var StbLanguageSys $Lang
     * @var StbDatabaseSys $Db
     */
    global $Bot, $Webhook, $Lang, $Db;
    DebugTrace();
    $admin = $Db->Admin($Webhook->User->Id);
    if(($admin->Perms & StbDbAdminPerm::Modules->value) === false):
      $Bot->TextSend(
        $Webhook->User->Id,
        $Lang->Get('Denied')
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
        $Db->CallBackHashSet(get_class() . '::Callback_ModuleAdd();')
      );
      $Bot->TextEdit(
        Admin,
        $Webhook->Message->Id,
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
        $Db->CallBackHashSet(get_class() . '::Callback_ModuleAdd();')
      );
      $Bot->TextEdit(
        Admin,
        $Webhook->Message->Id,
        $Lang->Get('UninstallNotFound', null, 'Module'),
        Markup: $mk
      );
      return;
    endif;
    call_user_func($Module . '::Install', $Bot, $Webhook, $Db);
  }

  static public function Callback_Mod(string $Module):void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var StbDatabaseSys $Db
     * @var StbLanguageSys $Lang
     * @var TgCallback $Webhook
     */
    global $Bot, $Db, $Lang, $Webhook;
    DebugTrace();
    $admin = $Db->Admin($Webhook->User->Id);
    if(($admin->Perms & StbDbAdminPerm::Modules->value) === false):
      $Bot->TextSend(
        $Webhook->User->Id,
        $Lang->Get('Denied')
      );
      return;
    endif;

    $mk = new TblMarkupInline;
    $line = 0;
    $col = 0;
    $date = $Db->Modules($Module);
    $mk->ButtonCallback(
      $line,
      $col++,
      $Lang->Get('Back'),
      $Db->CallBackHashSet(get_class() . '::Callback_Modules();')
    );
    $mk->ButtonCallback(
      $line,
      $col++,
      $Lang->Get('UninstallButton', Group: 'Module'),
      $Db->CallBackHashSet("Callback_UniModPic1('$Module');")
    );
    $Bot->TextEdit(
      Admin,
      $Webhook->Message->Id,
      sprintf(
        $Lang->Get('Module', Group: 'Module'),
        $Module,
        date('Y-m-d H:i:s', $date)
      ),
      Markup: $mk
    );
  }

  static public function Callback_UniModPic1(string $Module):void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var StbLanguageSys $Lang
     * @var TgCallback $Webhook
     * @var StbDatabaseSys $Db
     */
    global $Bot, $Lang, $Webhook, $Db;
    DebugTrace();
    $admin = $Db->Admin($Webhook->User->Id);
    if(($admin->Perms & StbDbAdminPerm::Modules->value) === false):
      $Bot->TextSend(
        $Webhook->User->Id,
        $Lang->Get('Denied')
      );
      return;
    endif;

    $mk = new TblMarkupInline;
    $mk->ButtonCallback(
      0,
      0,
      $Lang->Get('Back'),
      $Db->CallBackHashSet("Callback_Mod('$Module');")
    );
    $mk->ButtonCallback(
      0,
      1,
      $Lang->Get('Yes'),
      $Db->CallBackHashSet("Callback_UniModPic2('$Module');")
    );
    $Bot->MarkupEdit(
      Admin,
      $Webhook->Message->Id,
      Markup: $mk
    );
  }

  static public function Callback_UniModPic2(string $Module):void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var TgCallback $Webhook
     * @var StbDatabaseSys $Db
     * @var StbLanguageSys $Lang
     */
    global $Bot, $Webhook, $Db, $Lang;
    DebugTrace();
    $admin = $Db->Admin($Webhook->User->Id);
    if(($admin->Perms & StbDbAdminPerm::Modules->value) === false):
      $Bot->TextSend(
        $Webhook->User->Id,
        $Lang->Get('Denied')
      );
      return;
    endif;
    require(DirModules . '/' . $Module . '/index.php');
    call_user_func($Module . '::Uninstall', $Bot, $Webhook, $Db);
  }
}
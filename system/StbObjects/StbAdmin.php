<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2023.01.22.03

namespace ProtocolLive\SimpleTelegramBot\StbObjects;
use ProtocolLive\TelegramBotLibrary\TblObjects\{
  TblCmd,
  TblCommands,
  TblException,
  TblMarkupInline
};
use ProtocolLive\TelegramBotLibrary\TelegramBotLibrary;
use ProtocolLive\TelegramBotLibrary\TgObjects\{
  TgCallback,
  TgText
};

abstract class StbAdmin{
  public static function AdminAdd():void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var TgText $Webhook
     * @var StbDatabase $Db
     * @var StbLanguageSys $Lang
     */
    global $Bot, $Webhook, $Db, $Lang;
    DebugTrace();
    if(AdminCheck($Webhook->Data->User->Id, StbDbAdminPerm::Admins) === null
    or $Webhook->Text === Admin):
      return;
    endif;
    try{
      $user = $Bot->ChatGet($Webhook->Text);
    }catch(TblException){
      $Bot->TextSend(
        $Webhook->Data->User->Id,
        $Lang->Get('UserNull', Group: 'Errors')
      );
      return;
    }
    $mk = new TblMarkupInline;
    $mk->ButtonCallback(
      0,
      0,
      '🔙',
      $Db->CallBackHashSet([
        __CLASS__ . '::Callback_Admins'
      ])
    );
    $mk->ButtonCallback(
      0,
      1,
      '✅',
      $Db->CallBackHashSet([
        __CLASS__ . '::CallBack_Admin',
        $Webhook->Text,
        true
      ])
    );
    $name = $user->Name;
    if($user->NameLast !== null):
      $name .= ' ' . $user->NameLast;
    endif;
    if($user->Nick !== null):
      $name .= ' (@' . $user->Nick . ')';
    endif;
    $Db->ListenerDel(
      StbDbListeners::Text,
      $Webhook->Data->User->Id
    );
    $Db->VariableSet(
      StbDbVariables::Action->name,
      User: $Webhook->Data->User->Id
    );
    $Bot->TextSend(
      $Webhook->Data->User->Id,
      sprintf(
        $Lang->Get('AdminAddConfirm', Group: 'Admin'),
        $name
      ),
      Markup: $mk
    );
  }

  private static function JumpLineCheck(
    int &$Line,
    int &$Col,
    int $PerLine = 3
  ):void{
    DebugTrace();
    if($Col === $PerLine):
      $Col = 0;
      $Line++;
    endif;
  }

  public static function CmdAddName():void{
    /**
     * @var TgText $Webhook
     * @var StbDatabase $Db
     * @var TelegramBotLibrary $Bot
     * @var StbLanguageSys $Lang
     */
    global $Webhook, $Db, $Bot, $Lang;
    DebugTrace();
    if(AdminCheck($Webhook->Data->User->Id, StbDbAdminPerm::Cmds) === null):
      return;
    endif;
    $Db->VariableSet(
      StbDbVariables::CmdName->name,
      trim($Webhook->Text),
      __CLASS__,
      $Webhook->Data->User->Id
    );
    $Db->VariableSet(
      StbDbVariables::Action->name,
      StbDbVariables::CmdAddDescription->name,
      __CLASS__,
      $Webhook->Data->User->Id
    );
    $Bot->TextSend(
      $Webhook->Data->User->Id,
      $Lang->Get('CommandDescription', Group: 'Admin')
    );
  }

  public static function CmdAddDescription():void{
    /**
     * @var TgText $Webhook
     * @var StbDatabase $Db
     * @var TelegramBotLibrary $Bot
     */
    global $Webhook, $Db, $Bot;
    DebugTrace();
    if(AdminCheck($Webhook->Data->User->Id, StbDbAdminPerm::Cmds) === null):
      return;
    endif;
    $Db->VariableSet(
      StbDbVariables::Action->name,
      null,
      __CLASS__,
      $Webhook->Data->User->Id
    );
    $Db->ListenerDel(
      StbDbListeners::Text,
      $Webhook->Data->User->Id
    );
    $temp = $Db->VariableGet(
      StbDbVariables::CmdName->name,
      __CLASS__,
      $Webhook->Data->User->Id
    );
    $cmds = $Bot->MyCmdGet();
    $cmds->Add($temp, trim($Webhook->Text));
    $Bot->MyCmdSet($cmds);
    self::Callback_Commands();
  }

  public static function Command_id():void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var TblCmd $Webhook
     * @var StbDatabase $Db
     * @var StbLanguageSys $Lang
     */
    global $Bot, $Webhook, $Db, $Lang;
    DebugTrace();
    $Bot->TextSend(
      $Webhook->Data->User->Id,
      sprintf(
        $Lang->Get('MyId'),
        $Webhook->Data->User->Id,
      )
    );
    $Db->UsageLog($Webhook->Data->User->Id, 'id');
  }

  public static function Command_admin():void{
    DebugTrace();
    self::Callback_AdminMenu();
  }

  public static function Callback_AdminMenu():void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var TblCmd|TgCallback $Webhook
     * @var StbDatabase $Db
     * @var StbLanguageSys $Lang
     */
    global $Bot, $Webhook, $Db, $Lang;
    DebugTrace();
    if($Webhook instanceof TblCmd):
      $id = $Webhook->Data->User->Id;
    else:
      $id = $Webhook->User->Id;
    endif;
    $user = AdminCheck($id);
    if($user === null):
      return;
    endif;
    $mk = new TblMarkupInline;
    $line = 0;
    $col = 0;
    if($user->Perms & StbDbAdminPerm::Admins->value):
      $mk->ButtonCallback(
        $line,
        $col++,
        $Lang->Get('AdminsButton', Group: 'Admin'),
        $Db->CallBackHashSet([
          __CLASS__ . '::Callback_Admins'
        ])
      );
      self::JumpLineCheck($line, $col);
    endif;
    if($user->Perms & StbDbAdminPerm::Modules->value):
      $mk->ButtonCallback(
        $line,
        $col++,
        $Lang->Get('ModulesButton', Group: 'Admin'),
        $Db->CallBackHashSet([
          StbAdminModules::class . '::Callback_Modules'
        ])
      );
      self::JumpLineCheck($line, $col);
    endif;
    //Updates
    if($id === Admin):
      $mk->ButtonCallback(
        $line,
        $col++,
        $Lang->Get('UpdatesButton', Group: 'Admin'),
        $Db->CallBackHashSet([
          __CLASS__ . '::Callback_Updates'
        ])
      );
      self::JumpLineCheck($line, $col);
    endif;
    //Commands
    if($user->Perms & StbDbAdminPerm::Cmds->value):
      $mk->ButtonCallback(
        $line,
        $col++,
        $Lang->Get('CommandsButton', Group: 'Admin'),
        $Db->CallBackHashSet([
          __CLASS__ . '::Callback_Commands'
        ])
      );
      self::JumpLineCheck($line, $col);
    endif;
    //Info
    if($id === Admin):
      $mk->ButtonWebapp(
        $line,
        $col++,
        $Lang->Get('PhpInfoButton', Group: 'Admin'),
        dirname('https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']) . '/tools/info.php'
      );
      self::JumpLineCheck($line, $col);
    endif;
    //Stats
    if($user->Perms & StbDbAdminPerm::Stats->value):
      $mk->ButtonWebapp(
        $line,
        $col++,
        $Lang->Get('StatsButton', Group: 'Admin'),
        dirname('https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']) . '/stats.php'
      );
    endif;
    if($Webhook instanceof TblCmd):
      $Bot->TextSend(
        $Webhook->Data->User->Id,
        $Lang->Get('AdminMenu', Group: 'Admin'),
        Markup: $mk
      );
    else:
      $Bot->TextEdit(
        $Webhook->Data->Data->Chat->Id,
        $Webhook->Data->Data->Id,
        $Lang->Get('AdminMenu', Group: 'Admin'),
        Markup: $mk
      );
    endif;
  }

  public static function Callback_Admins():void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var TgCallback $Webhook
     * @var StbDatabase $Db
     * @var StbLanguageSys $Lang
     */
    global $Bot, $Webhook, $Db, $Lang;
    DebugTrace();
    if(AdminCheck($Webhook->User->Id, StbDbAdminPerm::Admins) === null):
      $Bot->CallbackAnswer(
        $Webhook->Id,
        $Lang->Get('Denied', Group: 'Errors')
      );
      return;
    endif;
    $mk = new TblMarkupInline();
    $mk->ButtonCallback(
      0,
      0,
      '🔙',
      $Db->CallBackHashSet([
        __CLASS__ . '::Callback_AdminMenu'
      ])
    );
    $mk->ButtonCallback(
      0,
      1,
      '➕',
      $Db->CallBackHashSet([
        __CLASS__ . '::Callback_AdminAdd'
      ])
    );
    $line = 1;
    $col = 0;
    $Admins = $Db->Admins();
    foreach($Admins as $admin):
      if($admin->Name === null):
        $detail = $admin;
      else:
        $detail = $admin->Name;
      endif;
      $mk->ButtonCallback(
        $line,
        $col++,
        $detail,
        $Db->CallBackHashSet([
          __CLASS__ . '::Callback_Admin',
          $admin->Id
        ])
      );
      self::JumpLineCheck($line, $col);
    endforeach;
    $Bot->TextEdit(
      $Webhook->Data->Data->Chat->Id,
      $Webhook->Data->Data->Id,
      $Lang->Get('Admins', Group: 'Admin'),
      Markup: $mk
    );
  }

  public static function Callback_AdminAdd():void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var TgCallback $Webhook
     * @var StbDatabase $Db
     * @var StbLanguageSys $Lang
     */
    global $Bot, $Webhook, $Db, $Lang;
    DebugTrace();
    if(AdminCheck($Webhook->User->Id, StbDbAdminPerm::Admins) === null):
      $Bot->CallbackAnswer(
        $Webhook->Id,
        $Lang->Get('Denied', Group: 'Errors')
      );
      return;
    endif;
    $Db->VariableSet(
      StbDbVariables::Action->name,
      StbDbVariables::AdminAdd->name,
      null,
      $Webhook->User->Id
    );
    $Db->ListenerAdd(
      StbDbListeners::Text,
      __CLASS__,
      $Webhook->User->Id
    );
    $mk = new TblMarkupInline;
    $mk->ButtonCallback(
      0,
      0,
      $Lang->Get('Cancel'),
      $Db->CallBackHashSet([__CLASS__ . '::Callback_Cancel'])
    );
    $Bot->TextSend(
      $Webhook->User->Id,
      $Lang->Get('AdminAddId', Group: 'Admin'),
      Markup: $mk
    );
  }

  public static function Callback_Admin(
    int $Admin,
    bool $ListenerDel = false
  ):void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var TgCallback $Webhook
     * @var StbDatabase $Db
     * @var StbLanguageSys $Lang
     */
    global $Bot, $Webhook, $Db, $Lang;
    DebugTrace();
    if(AdminCheck($Webhook->User->Id, StbDbAdminPerm::Admins) === null):
      $Bot->CallbackAnswer(
        $Webhook->Id,
        $Lang->Get('Denied', Group: 'Errors')
      );
      return;
    endif;
    $line = 0;
    $col = 0;
    $mk = new TblMarkupInline();
    $mk->ButtonCallback(
      $line,
      $col++,
      '🔙',
      $Db->CallBackHashSet([
        __CLASS__ . '::Callback_Admins'
      ])
    );
    self::JumpLineCheck($line, $col, 2);
    if($Admin !== Admin):
      $mk->ButtonCallback(
        $line,
        $col++,
        '🗑️',
        $Db->CallBackHashSet([
          __CLASS__ . '::Callback_AdminDel',
          $Admin
        ])
      );
      self::JumpLineCheck($line, $col, 2);
    endif;
    $admin = $Db->Admin($Admin);
    foreach(StbDbAdminPerm::cases() as $perm):
      if($perm === StbDbAdminPerm::All
      or $perm === StbDbAdminPerm::None):
        continue;
      endif;
      $value = $admin->Perms & $perm->value;
      $mk->ButtonCallback(
        $line,
        $col++,
        ($value ? '✅' : '') . $Lang->Get('Perm' . $perm->name, Group: 'Admin'),
        $Db->CallBackHashSet([
          __CLASS__ . '::Callback_AdminPerm',
          $Admin,
          $perm->value,
          !$value
        ])
      );
      self::JumpLineCheck($line, $col, 2);
    endforeach;
    $AdminName = $admin->Name;
    if($admin->NameLast !== null):
      $AdminName .= ' ' . $admin->NameLast;
    endif;
    if($admin->Nick !== null):
      $AdminName .= ' (' . $admin->Nick . ')';
    endif;
    $Bot->TextEdit(
      $Webhook->Data->Data->Chat->Id,
      $Webhook->Data->Data->Id,
      sprintf(
        $Lang->Get('Admin', Group: 'Admin'),
        $AdminName,
        date($Lang->Get('DateTime'), $admin->Creation)
      ),
      Markup: $mk
    );
    if($ListenerDel):
      $Db->ListenerDel(
        StbDbListeners::Text,
        __CLASS__,
        $Webhook->User->Id
      );
      $Db->VariableSet(
        StbDbVariables::AdminAdd->name,
        null,
        $Webhook->User->Id
      );
    endif;
  }

  public static function Callback_AdminDel(
    int $Id
  ):void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var TgCallback $Webhook
     * @var StbDatabase $Db
     * @var StbLanguageSys $Lang
     */
    global $Bot, $Webhook, $Db, $Lang;
    DebugTrace();
    if(AdminCheck($Webhook->User->Id, StbDbAdminPerm::Admins) === null
    or $Id === Admin):
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
      $Db->CallBackHashSet([
        __CLASS__ . '::Callback_Admin',
        $Id
      ])
    );
    $mk->ButtonCallback(
      0,
      1,
      $Lang->Get('Yes'),
      $Db->CallBackHashSet([
        __CLASS__ . '::Callback_AdminDel2',
        $Id
      ])
    );
    $Bot->TextEdit(
      $Webhook->Data->Data->Chat->Id,
      $Webhook->Data->Data->Id,
      $Lang->Get('AdminDel', Group: 'Admin'),
      Markup: $mk
    );
  }

  public static function Callback_AdminDel2(
    int $Id
  ):void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var TgCallback $Webhook
     * @var StbDatabase $Db
     * @var StbLanguageSys $Lang
     */
    global $Bot, $Webhook, $Db, $Lang;
    DebugTrace();
    if(AdminCheck($Webhook->User->Id, StbDbAdminPerm::Admins) === null
    or $Id === Admin):
      $Bot->CallbackAnswer(
        $Webhook->Id,
        $Lang->Get('Denied', Group: 'Errors')
      );
      return;
    endif;
    $Db->AdminEdit($Id, StbDbAdminPerm::None->value);
    self::Callback_Admins();
  }

  public static function Callback_AdminPerm(
    int $Admin,
    int $Perm,
    bool $Grant = false
  ):void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var TgCallback $Webhook
     * @var StbDatabase $Db
     * @var StbLanguageSys $Lang
     */
    global $Bot, $Webhook, $Db, $Lang;
    DebugTrace();
    if(AdminCheck($Webhook->User->Id, StbDbAdminPerm::Admins) === null
    or $Admin === Admin):
      $Bot->CallbackAnswer(
        $Webhook->Id,
        $Lang->Get('Denied', Group: 'Errors')
      );
      return;
    endif;
    $admin = $Db->Admin($Admin);
    if($Grant):
      $Perm = $admin->Perms | $Perm;
    else:
      $Perm = $admin->Perms & ~$Perm;
    endif;
    $Db->AdminEdit($Admin, $Perm);
    self::Callback_Admin($Admin);
  }

  public static function Callback_Cancel():void{
    /**
     * @var TgText $Webhook
     * @var StbDatabase $Db
     */
    global $Webhook, $Db;
    DebugTrace();
    $Db->VariableSet(
      StbDbVariables::Action->name,
      null,
      __CLASS__,
      $Webhook->Data->User->Id
    );
    $Db->ListenerDel(
      StbDbListeners::Text,
      $Webhook->Data->User->Id
    );
    self::Callback_Admins();
  }

  public static function Callback_CmdDown(
    string $Cmd
  ):void{
    /**
     * @var TelegramBotLibrary $Bot
     */
    global $Bot;
    DebugTrace();
    $CmdsNew = new TblCommands;
    $CmdsOld = $Bot->MyCmdGet()->Get();
    $DescrBackup = null;
    foreach($CmdsOld as $cmd => $descr):
      if($cmd === $Cmd):
        $DescrBackup = $descr;
        continue;
      else:
        $CmdsNew->Add($cmd, $descr);
      endif;
      if($DescrBackup !== null):
        $CmdsNew->Add($Cmd, $DescrBackup);
        $DescrBackup = null;
      endif;
    endforeach;
    $Bot->MyCmdSet($CmdsNew);
    self::Callback_Commands();
  }

  public static function Callback_CmdNew():void{
    /**
     * @var StbDatabase $Db
     * @var TgCallback $Webhook
     * @var TelegramBotLibrary $Bot
     * @var StbLanguageSys $Lang
     */
    global $Db, $Webhook, $Bot, $Lang;
    DebugTrace();
    if(AdminCheck($Webhook->User->Id, StbDbAdminPerm::Cmds) === null):
      return;
    endif;
    $Bot->TextEdit(
      $Webhook->Data->Data->Chat->Id,
      $Webhook->Data->Data->Id,
      $Lang->Get('CommandName', Group: 'Admin')
    );
    $Db->ListenerAdd(
      StbDbListeners::Text,
      __CLASS__,
      $Webhook->User->Id
    );
    $Db->VariableSet(
      StbDbVariables::Action->name,
      StbDbVariables::CmdAddName->name,
      __CLASS__,
      $Webhook->User->Id
    );
  }

  public static function Callback_CmdUp(
    string $Cmd
  ):void{
    /**
     * @var TelegramBotLibrary $Bot
     */
    global $Bot;
    DebugTrace();
    $CmdsNew = new TblCommands;
    $CmdsOld = $Bot->MyCmdGet()->Get();
    $BackupCmd = null;
    $BackupDescr = null;
    $first = true;
    $moved = false;
    foreach($CmdsOld as $cmd => $descr):
      if($first):
        $BackupCmd = $cmd;
        $BackupDescr = $descr;
      elseif($cmd == $Cmd):
        $CmdsNew->Add($cmd, $descr);
        $CmdsNew->Add($BackupCmd, $BackupDescr);
        $moved = true;
      elseif($moved):
        $CmdsNew->Add($cmd, $descr);
      else:
        $CmdsNew->Add($BackupCmd, $BackupDescr);
        $BackupCmd = $cmd;
        $BackupDescr = $descr;
      endif;
      $first = false;
    endforeach;
    $Bot->MyCmdSet($CmdsNew);
    self::Callback_Commands();
  }

  public static function Callback_Commands():void{
    /**
     * @var StbDatabase $Db
     * @var TgCallback|TgText $Webhook
     * @var TelegramBotLibrary $Bot
     * @var StbLanguageSys $Lang
     */
    global $Db, $Webhook, $Bot, $Lang;
    DebugTrace();
    if($Webhook instanceof TgCallback):
      $temp = $Webhook->User->Id;
    else:
      $temp = $Webhook->Data->User->Id;
    endif;
    if(AdminCheck($temp, StbDbAdminPerm::Cmds) === null):
      return;
    endif;
    $mk = new TblMarkupInline;
    $line = 0;
    $col = 0;
    $i = 0;
    $mk->ButtonCallback(
      $line,
      0,
      '🔙',
      $Db->CallBackHashSet([__CLASS__ . '::Command_admin'])
    );
    $mk->ButtonCallback(
      $line++,
      1,
      '🆕',
      $Db->CallBackHashSet([__CLASS__ . '::Callback_CmdNew'])
    );
    $cmds = $Bot->MyCmdGet()->Get();
    $last = count($cmds) - 1;
    foreach($cmds as $cmd => $descr):
      $mk->ButtonCallback(
        $line,
        $col++,
        $cmd,
        $Db->CallBackHashSet([__CLASS__ . '::Callback_CmdEdit', $cmd])
      );
      if($i < $last):
        $mk->ButtonCallback(
          $line,
          $col++,
          '🔽',
          $Db->CallBackHashSet([__CLASS__ . '::Callback_CmdDown', $cmd])
        );
      endif;
      if($i > 0):
        $mk->ButtonCallback(
          $line,
          $col++,
          '🔼',
          $Db->CallBackHashSet([__CLASS__ . '::Callback_CmdUp', $cmd])
        );
      endif;
      $i++;
      $line++;
      $col = 0;
    endforeach;
    if($Webhook instanceof TgCallback):
      $Bot->TextEdit(
        $Webhook->Data->Data->Chat->Id,
        $Webhook->Data->Data->Id,
        $Lang->Get('CommandsButton', Group: 'Admin'),
        Markup: $mk
      );
    else:
      $Bot->TextSend(
        $Webhook->Data->Chat->Id,
        $Lang->Get('CommandsButton', Group: 'Admin'),
        Markup: $mk
      );
    endif;
  }

  public static function Callback_Updates():void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var TgCallback $Webhook
     * @var StbDatabase $Db
     * @var StbLanguageSys $Lang
     */
    global $Bot, $Webhook, $Db, $Lang;
    DebugTrace();
    if($Webhook->User->Id !== Admin):
      $Bot->TextSend(
        $Webhook->User->Id,
        $Lang->Get('Denied', Group: 'Errors')
      );
      return;
    endif;
    $mk = new TblMarkupInline();
    $line = 0;
    $col = 0;
    $mk->ButtonCallback(
      $line,
      $col++,
      '🔙',
      $Db->CallBackHashSet([
        __CLASS__ . '::Callback_AdminMenu'
      ])
    );
    $stb = file_get_contents('https://raw.githubusercontent.com/ProtocolLive/SimpleTelegramBot/main/sha1sum.txt');
    $stb = str_replace("\n", "\r\n", $stb);
    $stb = file_get_contents(DirSystem . '\sha1sum.txt') === $stb;
    if($stb):
      $stb = $Lang->Get('Yes');
    else:
      $stb = $Lang->Get('No');
    endif;
    $tbl = file_get_contents('https://raw.githubusercontent.com/ProtocolLive/TelegramBotLibrary/main/src/sha1sum.txt');
    $tbl = str_replace("\n", "\r\n", $tbl);
    $tbl = file_get_contents(DirSystem . '\vendor\protocollive\telegrambotlibrary\src\sha1sum.txt') === $tbl;
    if($tbl):
      $tbl = $Lang->Get('Yes');
    else:
      $tbl = $Lang->Get('No');
    endif;
    $Bot->TextEdit(
      $Webhook->Data->Data->Chat->Id,
      $Webhook->Data->Data->Id,
      sprintf(
        $Lang->Get('Updates', Group: 'Admin'),
        $stb,
        $tbl
      ),
      Markup: $mk
    );
  }

  public static function Listener_Text():bool{
    /**
     * @var TgText $Webhook
     * @var StbDatabase $Db
     */
    global $Webhook, $Db;
    DebugTrace();
    $temp = $Db->VariableGet(
      StbDbVariables::Action->name,
      __CLASS__,
      $Webhook->Data->User->Id
    );
    if($temp === null):
      return true;
    elseif($temp === StbDbVariables::AdminAdd->name):
      self::AdminAdd();
      return false;
    elseif($temp === StbDbVariables::CmdAddName->name):
      self::CmdAddName();
      return false;
    elseif($temp === StbDbVariables::CmdAddDescription->name):
      self::CmdAddDescription();
      return false;
    endif;
  }
}
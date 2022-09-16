<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.09.16.00

namespace ProtocolLive\SimpleTelegramBot\StbObjects;
use ProtocolLive\SimpleTelegramBot\StbObjects\{
  StbAdminModules, StbDbVariables
};
use ProtocolLive\TelegramBotLibrary\TblObjects\{
  TblMarkupInline, TblMarkupForceReply, TblCmd, TblEntities, TblException
};
use ProtocolLive\TelegramBotLibrary\TelegramBotLibrary;
use ProtocolLive\TelegramBotLibrary\TgObjects\{
  TgCallback, TgEntity, TgEntityType, TgProfilePhoto, TgText
};

class StbAdmin{
  static private function JumpLineCheck(
    int &$Line,
    int &$Col,
    int $PerLine = 3
  ):void{
    if($Col === $PerLine):
      $Col = 0;
      $Line++;
    endif;
  }

  static public function Command_id():void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var StbLanguageSys $Lang
     * @var TblCmd $Webhook
     * @var StbDatabase $Db
     */
    global $Bot, $Lang, $Webhook, $Db;
    DebugTrace();
    $Bot->TextSend(
      $Webhook->Message->User->Id,
      sprintf(
        $Lang->Get('MyId'),
        $Webhook->Message->User->Id,
      )
    );
    $Db->UsageLog($Webhook->Message->User->Id, 'id');
  }

  static public function Command_admin():void{
    self::Callback_AdminMenu();
  }

  static public function Callback_AdminMenu():void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var TblCmd|TgCallback $Webhook
     * @var StbLanguageSys $Lang
     * @var StbDatabase $Db
     */
    global $Bot, $Webhook, $Lang, $Db;
    DebugTrace();
    $id = $Webhook->User->Id ?? $Webhook->Message->User->Id;
    $user = AdminCheck($id);
    if($user === null):
      return;
    endif;
    $mk = new TblMarkupInline();
    $line = 0;
    $col = 0;
    if($user->Perms->value & StbDbAdminPerm::Admins->value):
      $mk->ButtonCallback(
        $line,
        $col++,
        $Lang->Get('AdminsButton', Group: 'Admin'),
        $Db->CallBackHashSet([
          __CLASS__ . '::Callback_Admins'
        ])
      );
    endif;
    self::JumpLineCheck($line, $col);
    if($user->Perms->value & StbDbAdminPerm::Modules->value):
      $mk->ButtonCallback(
        $line,
        $col++,
        $Lang->Get('ModulesButton', Group: 'Admin'),
        $Db->CallBackHashSet([
          StbAdminModules::class . '::Callback_Modules'
        ])
      );
    endif;
    self::JumpLineCheck($line, $col);
    if($id === Admin):
      $mk->ButtonCallback(
        $line,
        $col++,
        $Lang->Get('UpdatesButton', Group: 'Admin'),
        $Db->CallBackHashSet([
          __CLASS__ . '::Callback_Updates'
        ])
      );
    endif;
    self::JumpLineCheck($line, $col);
    if($id === Admin):
      $mk->ButtonWebapp(
        $line,
        $col++,
        $Lang->Get('PhpInfoButton', Group: 'Admin'),
        dirname('https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']) . '/tools/info.php'
      );
    endif;
    self::JumpLineCheck($line, $col);
    if($user->Perms->value & StbDbAdminPerm::Stats->value):
      $mk->ButtonWebapp(
        $line,
        $col++,
        $Lang->Get('StatsButton', Group: 'Admin'),
        dirname('https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']) . '/stats.php'
      );
    endif;
    if(get_class($Webhook) === TblCmd::class):
      $Bot->TextSend(
        $Webhook->Message->User->Id,
        $Lang->Get('AdminMenu', Group: 'Admin'),
        Markup: $mk
      );
    else:
      $Bot->TextEdit(
        $Webhook->User->Id,
        $Webhook->Message->Id,
        $Lang->Get('AdminMenu', Group: 'Admin'),
        Markup: $mk
      );
    endif;
  }

  static public function Callback_Admins():void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var TgCallback $Webhook
     * @var StbDatabase $Db
     * @var StbLanguageSys $Lang
     */
    global $Bot, $Webhook, $Db, $Lang;
    DebugTrace();
    if(AdminCheck($Webhook->User->Id, StbDbAdminPerm::Admins) === null):
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
        __CLASS__ . '::Callback_AdminNew'
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
          $admin
        ])
      );
      self::JumpLineCheck($line, $col);
    endforeach;
    $Bot->TextEdit(
      $Webhook->User->Id,
      $Webhook->Message->Id,
      $Lang->Get('Admins', Group: 'Admin'),
      Markup: $mk
    );
  }

  static public function Callback_AdminNew():void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var TgCallback $Webhook
     * @var StbDatabase $Db
     * @var StbLanguageSys $Lang
     */
    global $Bot, $Webhook, $Db, $Lang;
    $msg = $Bot->TextSend(
      $Webhook->User->Id,
      $Lang->Get('AdminNewId', Group: 'Admin'),
      Markup: new TblMarkupForceReply
    );
    if($msg !== null):
      $Db->ListenerAdd(
        StbDbListeners::Text,
        __CLASS__,
        $Webhook->User->Id
      );
      $Db->VariableSet(
        StbDbVariables::AdminNew->name,
        $msg->Message->Id,
        $Webhook->User->Id
      );
    endif;
  }

  public static function Callback_AdminNewOk(int $Id):void{
    /**
     * @var StbDatabase $Db
     * @var TelegramBotLibrary $Bot
     * @var StbLanguageSys $Lang
     * @var TgCallback $Webhook
     */
    global $Bot, $Lang, $Webhook;
    self::CallBack_Admin($Id);
    $Bot->TextSend($Id, $Lang->Get('AdminPromoted', Group: 'Admin'));
  }

  static public function Callback_Admin(int $Admin):void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var TgCallback $Webhook
     * @var StbDatabase $Db
     * @var StbLanguageSys $Lang
     */
    global $Bot, $Webhook, $Db, $Lang;
    DebugTrace();
    if(AdminCheck($Webhook->User->Id, StbDbAdminPerm::Admins) === null):
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
    $admin = $Db->Admin($Admin);
    foreach(StbDbAdminPerm::cases() as $perm):
      if($perm === StbDbAdminPerm::All
      or $perm === StbDbAdminPerm::None):
        continue;
      endif;
      $value = $admin->Perms->value & $perm->value;
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
      $Webhook->User->Id,
      $Webhook->Message->Id,
      sprintf(
        $Lang->Get('Admin', Group: 'Admin'),
        $AdminName,
        date($Lang->Get('DateTime'), $admin->Creation)
      ),
      Markup: $mk
    );
  }

  static public function Callback_AdminPerm(
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
    global $Webhook, $Db;
    DebugTrace();
    if(AdminCheck($Webhook->User->Id, StbDbAdminPerm::Admins) === null):
      return;
    endif;
    $admin = $Db->Admin($Admin);
    if($Grant):
      $Perm = $admin->Perms->value | $Perm;
    else:
      $Perm = $admin->Perms->value & ~$Perm;
    endif;
    $Db->AdminEdit($Admin, $Perm);
    self::Callback_Admin($Admin);
  }

  static public function Callback_Updates():void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var TgCallback $Webhook
     * @var StbLanguageSys $Lang
     * @var StbDatabase $Db
     */
    global $Bot, $Webhook, $Lang, $Db;
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
    $files = UpdateCheck();
    $files = implode(PHP_EOL, $files);
    $Bot->TextEdit(
      Admin,
      $Webhook->Message->Id,
      sprintf(
        $Lang->Get('Updates', Group: 'Admin'),
        $files
      ),
      Markup: $mk
    );
  }

  static public function Listener_Text():bool{
    /**
     * @var StbDatabase $Db
     * @var TgText $Webhook
     * @var TelegramBotLibrary $Bot
     * @var StbLanguageSys $Lang
     */
    global $Db, $Webhook, $Bot, $Lang;
    $msg = $Db->VariableGet(StbDbVariables::AdminNew->name, $Webhook->Message->User->Id);
    if($msg === null
    or $Webhook->Message->Reply === null
    or $msg != $Webhook->Message->Reply->Message->Id):
      return true;
    endif;
    try{
      $user = $Bot->ChatGet($Webhook->Text);
    }catch(TblException $e){
      $Bot->TextSend(
        $Webhook->Message->User->Id,
        $Lang->Get('UserNull', Group: 'Errors')
      );
      return false;
    }
    $Db->UserEdit(Tgchat2Tguser($user));
    $mk = new TblMarkupInline;
    $mk->ButtonCallback(0, 0, '🔙', $Db->CallBackHashSet([__CLASS__ . '::Callback_Admins']));
    $mk->ButtonCallback(0, 1, '✅', $Db->CallBackHashSet([__CLASS__ . '::Callback_AdminNewOk', $Webhook->Text]));
    $name = $user->Name;
    if($user->NameLast !== null):
      $name .= ' ' . $user->NameLast;
    endif;
    if($user->Nick !== null):
      $name .= ' (@' . $user->Nick . ')';
    endif;
    $Bot->TextSend(
      $Webhook->Message->User->Id,
      sprintf(
        $Lang->Get('AdminNewConfirm', Group: 'Admin'),
        $name
      ),
      Markup: $mk
    );
    return false;
  }
}
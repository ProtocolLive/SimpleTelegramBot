<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.08.19.00

use ProtocolLive\TelegramBotLibrary\TblObjects\{
  TblMarkupInline, TblMarkupForceReply, TblCmd, TgCallback
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
    $user = $Db->Admin($id);
    if($user === false):
      $Bot->TextSend(
        $id,
        $Lang->Get('Denied')
      );
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
          get_class() . '::Callback_Admins'
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
          'StbAdminModules::Callback_Modules'
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
          get_class() . '::Callback_Updates'
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
    $user = $Db->Admin($Webhook->User->Id);
    if(($user->Perms->value & StbDbAdminPerm::Admins->value) === false):
      $Bot->TextSend(
        $Webhook->User->Id,
        $Lang->Get('Denied')
      );
      return;
    endif;
    $mk = new TblMarkupInline();
    $mk->ButtonCallback(
      0,
      0,
      '🔙',
      $Db->CallBackHashSet([
        get_class() . '::Callback_AdminMenu'
      ])
    );
    $mk->ButtonCallback(
      0,
      1,
      '➕',
      $Db->CallBackHashSet([
        get_class() . '::Callback_AdminNew'
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
          get_class() . '::Callback_Admin',
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
        get_class(),
        $Webhook->User->Id
      );
      $Db->VariableSet(
        'AdminNew',
        $msg->Id
      );
    endif;
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
    $user = $Db->Admin($Webhook->User->Id);
    if($user === null
    or ($user->Perms->value & StbDbAdminPerm::Admins->value) === false):
      $Bot->TextSend(
        $Webhook->User->Id,
        $Lang->Get('Denied')
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
        get_class() . '::Callback_Admins'
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
          get_class() . '::Callback_AdminPerm',
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
    global $Bot, $Webhook, $Db, $Lang;
    DebugTrace();
    $user = $Db->Admin($Webhook->User->Id);
    if($user === null
    or ($user->Perms->value & StbDbAdminPerm::Admins->value) === false):
      $Bot->TextSend(
        $Webhook->User->Id,
        $Lang->Get('Denied')
      );
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
        $Lang->Get('Denied')
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
        get_class() . '::Callback_AdminMenu'
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
     * @var TelegramBotLibrary $Bot
     * @var TgText $Webhook
     * @var StbDatabase $Db
     * @var StbLanguageSys $Lang
     */
    global $Bot, $Webhook, $Db, $Lang;
    if(get_class($Webhook->Message->Reply) !== TgText::class):
      return true;
    endif;
    if($Db->VariableGet('AdminNew') !== $Webhook->Message->Reply->Message->Id):
      return true;
    endif;
    /*
    $Db->ListenerDel(
      StbDbListeners::TextReply,
      'Listener_AdminNew',
      $Webhook->User->Id
    );
    $Db->VariableDel('AdminNew');
    */
    $details = $Bot->ChatGet($Webhook->Text);
    if($details === null):
      $Bot->TextSend(
        $Webhook->User->Id,
        $Lang->Get('UserNull', Group: 'Errors')
      );
      return false;
    endif;
    $Db->UserEdit(
      $Webhook->Text,
      $details->Name,
      $details->Type,
      $details->NameLast,
      $details->Nick
    );
    $Bot->TextSend(
      $Webhook->Message->User->Id,
      sprintf(
        $Lang->Get('UserFound', Group: 'Admin'),
        $details->Name
      )
    );
  }
}
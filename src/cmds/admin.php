<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.06.18.04

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
     */
    global $Bot, $Lang, $Webhook;
    DebugTrace();
    $Bot->TextSend(
      $Webhook->Message->User->Id,
      sprintf(
        $Lang->Get('MyId'),
        $Webhook->Message->User->Id,
      )
    );
    LogUsage('id');
  }

  static public function Command_admin():void{
    self::Callback_AdminMenu();
  }

  static public function Callback_AdminMenu():void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var TblCmd|TgCallback $Webhook
     * @var StbLanguageSys $Lang
     * @var StbDatabaseSys $Db
     */
    global $Bot, $Webhook, $Lang, $Db;
    DebugTrace();
    $id = $Webhook->User->Id ?? $Webhook->Message->User->Id;
    $admins = $Db->Admins();
    if(isset($admins[$id]) === false):
      $Bot->TextSend(
        $id,
        $Lang->Get('Denied')
      );
      return;
    endif;

    $mk = new TblMarkupInline();
    $line = 0;
    $col = 0;
    if($admins[$id]->Perms->value & StbDbAdminPerm::Admins->value):
      $mk->ButtonCallback(
        $line,
        $col++,
        $Lang->Get('AdminsButton', Group: 'Admin'),
        $Db->CallBackHashSet(get_class() . '::Callback_Admins();')
      );
    endif;
    self::JumpLineCheck($line, $col);
    if($admins[$id]->Perms->value & StbDbAdminPerm::Modules->value):
      $mk->ButtonCallback(
        $line,
        $col++,
        $Lang->Get('ModulesButton', Group: 'Admin'),
        $Db->CallBackHashSet('StbAdminModules::Callback_Modules();')
      );
    endif;
    self::JumpLineCheck($line, $col);
    if($id === Admin):
      $mk->ButtonCallback(
        $line,
        $col++,
        $Lang->Get('UpdatesButton', Group: 'Admin'),
        $Db->CallBackHashSet(get_class() . '::Callback_Updates();')
      );
    endif;
    self::JumpLineCheck($line, $col);
    if($id === Admin):
      $mk->ButtonWebapp(
        $line,
        $col++,
        $Lang->Get('PhpInfoButton', Group: 'Admin'),
        dirname($_SERVER['SCRIPT_URI']) . '/tools/info.php'
      );
    endif;
    self::JumpLineCheck($line, $col);
    if($admins[$id]->Perms->value & StbDbAdminPerm::Stats->value):
      $mk->ButtonWebapp(
        $line,
        $col++,
        $Lang->Get('StatsButton', Group: 'Admin'),
        dirname($_SERVER['SCRIPT_URI']) . '/stats.php'
      );
    endif;
    if(get_class($Webhook) === 'TblCmd'):
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
     * @var StbDatabaseSys $Db
     * @var StbLanguageSys $Lang
     */
    global $Bot, $Webhook, $Db, $Lang;
    DebugTrace();
    $admins = $Db->Admins();
    if(($admins[$Webhook->User->Id]->Perms & StbDbAdminPerm::Admins->value) === false):
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
      $Db->CallBackHashSet(get_class() . '::Callback_AdminMenu();')
    );
    $mk->ButtonCallback(
      0,
      1,
      '➕',
      $Db->CallBackHashSet(get_class() . '::Callback_AdminNew();')
    );
    $line = 1;
    $col = 0;
    $Admins = $Db->Admins();
    $buttons = [];
    foreach($Admins as $admin => $data):
      $detail = $Db->VariableGet(StbDbParam::UserDetails->value, $admin);
      if($detail === null):
        $detail = $admin;
      else:
        $detail = $detail['Name'];
      endif;
      $buttons[$admin] = [$line, $col];
      $mk->ButtonCallback(
        $line,
        $col++,
        $detail,
        $Db->CallBackHashSet(get_class() . "::Callback_Admin($admin);")
      );
      self::JumpLineCheck($line, $col);
    endforeach;
    $Bot->TextEdit(
      $Webhook->User->Id,
      $Webhook->Message->Id,
      $Lang->Get('Admins', Group: 'Admin'),
      Markup: $mk
    );

    $changed = false;
    foreach($buttons as $admin => $coord):
      $detail = $Bot->ChatGet($admin);
      if($detail !== null):
        $Db->VariableSet(StbDbParam::UserDetails->value, $detail, $admin);
        $data = $mk->ButtonGet($coord[0], $coord[1]);
        if($data['text'] !== $detail->Name):
          $changed = true;
          $mk->ButtonCallback(
            $coord[0],
            $coord[1],
            $detail->Name,
            $Db->CallBackHashSet("Callback_Admin($admin)")
          );
        endif;
      endif;
    endforeach;
    if($changed):
      $Bot->MarkupEdit(
        $Webhook->User->Id,
        $Webhook->Message->Id,
        Markup: $mk
      );
    endif;
  }

  static public function Callback_AdminNew():void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var TgCallback $Webhook
     * @var StbDatabaseSys $Db
     * @var StbLanguageSys $Lang
     */
    global $Bot, $Webhook, $Db, $Lang;
    $msg = $Bot->TextSend(
      $Webhook->User->Id,
      $Lang->Get('AdminNewId', Group: 'Admin'),
      Markup: new TblMarkupForceReply
    );
    $Db->ListenerAdd(
      StbDbListeners::Text,
      'Listener_AdminNew',
      $Webhook->User->Id
    );
    if($msg !== null):
      $Db->VariableSet(
        'AdminNew',
        $msg->Id
      );
    endif;
  }

  static public function Callback_Updates():void{
    /**
     * @var TelegramBotLibrary $Bot
     * @var TgCallback $Webhook
     * @var StbLanguageSys $Lang
     * @var StbDatabaseSys $Db
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
      $Db->CallBackHashSet(get_class() . '::Callback_AdminMenu();')
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
     * @var StbDatabaseSys $Db
     * @var StbLanguageSys $Lang
     */
    global $Bot, $Webhook, $Db, $Lang;
    if(get_class($Webhook->Message->Reply) !== 'TgText'):
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
    $Db->VariableSet(StbDbParam::UserDetails->value, $details, $Webhook->Text);
    $Bot->TextSend(
      $Webhook->Message->User->Id,
      sprintf(
        $Lang->Get('UserFound', Group: 'Admin'),
        $details->Name
      )
    );
  }
}
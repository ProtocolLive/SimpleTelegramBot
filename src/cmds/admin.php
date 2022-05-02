<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.05.01.03

function Command_id():void{
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
      $Lang->Get('MyId', Group: 'Admin'),
      $Webhook->Message->User->Id,
    )
  );
  LogEvent('id');
}

function Command_admin():void{
  Callback_AdminMenu();
}

function Callback_AdminMenu():void{
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
  if($admins[$id][StbDatabaseSys::AdminDataPerm] & StbDatabaseSys::AdminAdmins):
    $mk->ButtonCallback(
      $line,
      $col++,
      $Lang->Get('AdminsButton', Group: 'Admin'),
      'Admins'
    );
  endif;
  JumpLineCheck($line, $col);
  if($admins[$id][StbDatabaseSys::AdminDataPerm] & StbDatabaseSys::AdminModules):
    $mk->ButtonCallback(
      $line,
      $col++,
      $Lang->Get('ModulesButton', Group: 'Admin'),
      'Modules'
    );
  endif;
  JumpLineCheck($line, $col);
  if($id === Admin):
    $mk->ButtonCallback(
      $line,
      $col,
      $Lang->Get('UpdatesButton', Group: 'Admin'),
      'Updates'
    );
  endif;
  JumpLineCheck($line, $col);
  if($id === Admin):
    $mk->ButtonWebapp(
      1,
      0,
      $Lang->Get('PhpInfoButton', Group: 'Admin'),
      dirname($_SERVER['SCRIPT_URI']) . '/tools/info.php'
    );
  endif;
  JumpLineCheck($line, $col);
  if($admins[$id][StbDatabaseSys::AdminDataPerm] & StbDatabaseSys::AdminStats):
    $mk->ButtonWebapp(
      1,
      1,
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

function Callback_Admins():void{
  /**
   * @var TelegramBotLibrary $Bot
   * @var TgCallback $Webhook
   * @var StbDatabaseSys $Db
   * @var StbLanguageSys $Lang
   */
  global $Bot, $Webhook, $Db, $Lang;
  DebugTrace();
  $admins = $Db->Admins();
  if(($admins[$Webhook->User->Id][StbDatabaseSys::AdminDataPerm] & StbDatabaseSys::AdminAdmins) === false):
    $Bot->TextSend(
      $Webhook->User->Id,
      $Lang->Get('Denied')
    );
    return;
  endif;
  $mk = new TblMarkupInline();
  $line = 0;
  $col = 0;
  $mk->ButtonCallback($line, $col++, '🔙', 'AdminMenu');
  $mk->ButtonCallback($line, $col++, '➕', 'AdminNew');

  $Admins = $Db->Admins();
  $buttons = [];
  foreach($Admins as $admin => $data):
    $detail = $Db->VariableGet(StbDatabaseSys::ParamUserDetails, $admin);
    if($detail === null):
      $detail = $admin;
    else:
      $detail = $detail['Name'];
    endif;
    $buttons[$admin] = [$line, $col];
    $mk->ButtonCallback($line, $col++, $detail, 'Admin ' . $admin);
    JumpLineCheck($line, $col);
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
      $Db->VariableSet(StbDatabaseSys::ParamUserDetails, $detail, $admin);
      $data = $mk->ButtonGet($coord[0], $coord[1]);
      if($data['text'] !== $detail->Name):
        $changed = true;
        $mk->ButtonCallback($coord[0], $coord[1], $detail->Name, 'Admin ' . $admin);
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

function Callback_Updates():void{
  /**
   * @var TelegramBotLibrary $Bot
   * @var TgCallback $Webhook
   * @var StbLanguageSys $Lang
   */
  global $Bot, $Webhook, $Lang;
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
  $mk->ButtonCallback($line, $col++, '🔙', 'AdminMenu');
  $files = UpdateCheck();
  $files = implode("\n", $files);
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

function JumpLineCheck(int &$Line, int &$Col):void{
  if($Col === 3):
    $Col = 0;
    $Line++;
  endif;
}
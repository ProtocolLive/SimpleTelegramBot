<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.04.30.03

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
   */
  global $Bot, $Webhook, $Lang;
  DebugTrace();
  if(($Webhook->User->Id ?? $Webhook->Message->User->Id) !== Admin):
    $Bot->TextSend(
      $Webhook->User->Id ?? $Webhook->Message->User->Id,
      $Lang->Get('Denied')
    );
    return;
  endif;
  $mk = new TblMarkupInline();
  $mk->ButtonCallback(
    0,
    0,
    $Lang->Get('AdminsButton', Group: 'Admin'),
    'Admins'
  );
  $mk->ButtonCallback(
    0,
    1,
    $Lang->Get('ModulesButton', Group: 'Admin'),
    'Modules'
  );
  $mk->ButtonCallback(
    0,
    2,
    $Lang->Get('UpdatesButton', Group: 'Admin'),
    'Updates'
  );
  $mk->ButtonWebapp(
    1,
    0,
    $Lang->Get('PhpInfoButton', Group: 'Admin'),
    dirname($_SERVER['SCRIPT_URI']) . '/tools/info.php'
  );
  $mk->ButtonWebapp(
    1,
    1,
    $Lang->Get('StatsButton', Group: 'Admin'),
    dirname($_SERVER['SCRIPT_URI']) . '/stats.php'
  );
  if(get_class($Webhook) === 'TblCmd'):
    $Bot->TextSend(
      Admin,
      $Lang->Get('AdminMenu', Group: 'Admin'),
      Markup: $mk
    );
  else:
    $Bot->TextEdit(
      Admin,
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
  $mk->ButtonCallback($line, $col++, '➕', 'AdminNew');

  $Admins = [Admin => 0] + $Db->Admins();
  foreach($Admins as $admin => $time):
    $detail = $Db->VariableGet(StbDatabaseSys::ParamUserDetails, $admin);
    if($detail === null):
      $detail = $admin;
    else:
      $detail = $detail['Name'];
    endif;
    $mk->ButtonCallback($line, $col++, $detail, 'Admin ' . $admin);
  endforeach;
  $Bot->TextEdit(
    Admin,
    $Webhook->Message->Id,
    $Lang->Get('Admins', Group: 'Admin'),
    Markup: $mk
  );

  foreach($Admins as $admin => $time):
    $detail = $Bot->ChatGet($admin);
    if($detail !== null):
      $Db->VariableSet(StbDatabaseSys::ParamUserDetails, $detail, $admin);
    endif;
  endforeach;
}

function Callback_Updates():void{
  /**
   * @var TelegramBotLibrary $Bot
   * @var TgCallback $Webhook
   * @var StbDatabaseSys $Db
   * @var StbLanguageSys $Lang
   */
  global $Bot, $Webhook, $Db, $Lang;
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
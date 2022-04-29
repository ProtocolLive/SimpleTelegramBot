<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.04.29.01

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
}

function Command_admin():void{
  Callback_admin();
}

function Callback_admin():void{
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
  $line = 0;
  $col = 0;
  $mk->ButtonCallback(
    $line,
    $col++,
    $Lang->Get('AdminsButton', Group: 'Admin'),
    'Admins'
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

function Callback_admins():void{
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
  $mk->ButtonCallback($line, $col++, '🔙', 'admin');
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
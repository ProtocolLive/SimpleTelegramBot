<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.04.29.00

function Command_id():void{
  /**
   * @var TelegramBotLibrary $Bot
   * @var StbLanguage $Lang
   * @var TblCmd $Webhook
   */
  global $Bot, $Lang, $Webhook;
  $Bot->TextSend(
    $Webhook->Message->User->Id,
    sprintf(
      $Lang->Get('MyId', Group: 'Admin'),
      $Webhook->Message->User->Id,
    )
  );
}

function Command_admin():void{
  /**
   * @var TelegramBotLibrary $Bot
   * @var TblCmd $Webhook
   * @var StbDatabaseSys $Db
   * @var StbLanguage $Lang
   */
  global $Bot, $Webhook, $Db, $Lang;
  if($Webhook->Message->User->Id !== Admin):
    $Bot->TextSend(
      $Webhook->Message->User->Id,
      $Lang->Get('Denied')
    );
    return;
  endif;
  $mk = new TblMarkupInline();
  $line = 0;
  $col = 0;
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
  $Bot->TextSend(
    Admin,
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
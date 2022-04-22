<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.04.21.00

function Command_admin():void{
  /**
   * @var TelegramBotLibrary $Bot
   * @var TgCmd $Webhook
   * @var StbSysDatabase $Db
   * @var StbLanguage $Lang
   */
  global $Bot, $Webhook, $Db, $Lang;
  if($Webhook->User->Id !== Admin):
    $Bot->SendText(
      $Webhook->User->Id,
      $Lang->Get('Denied')
    );
    return;
  endif;
  $mk = new TblMarkupInline();
  $line = 0;
  $col = 0;
  $mk->ButtonCallback($line, $col++, '➕', 'AdminNew');
  $todo = [];

  $detail = $Db->Variable(StbSysDatabase::ParamUserDetails, null, Admin);
  if($detail === null):
    $detail = Admin;
    $todo[] = Admin;
  else:
    $detail = $detail['Name'];
  endif;
  $mk->ButtonCallback($line, $col++, $detail, 'Admin ' . Admin);

  foreach($Db->Admins() as $admin => $time):
    $detail = $Db->Variable(StbSysDatabase::ParamUserDetails, null, $admin);
    if($detail === null):
      $detail = $admin;
      $todo[] = $admin;
    else:
      $detail = $detail['Name'];
    endif;
    $mk->ButtonCallback($line, $col++, $admin, 'Admin ' . $admin);
  endforeach;
  $Bot->SendText(
    Admin,
    $Lang->Get('Admins', Group: 'Admin'),
    Markup: $mk
  );

  foreach($todo as $user):
    $detail = $Bot->ChatGet($user);
    if($detail !== null):
      vd($detail);
      $Db->Variable(StbSysDatabase::ParamUserDetails, $detail, $user);
    endif;
  endforeach;
}
<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2023.02.02.01

//This file are included by DirBot/index.php

use ProtocolLive\SimpleTelegramBot\StbObjects\{
  StbBotTools,
  StbDatabase,
  StbDbListeners,
  StbLanguageSys,
  StbModuleTools
};
use ProtocolLive\TelegramBotLibrary\TblObjects\{
  TblCmd,
  TblData,
  TblException,
  TblWebhook
};
use ProtocolLive\TelegramBotLibrary\TelegramBotLibrary;
use ProtocolLive\TelegramBotLibrary\TgObjects\{
  TgCallback,
  TgChat,
  TgChatTitle,
  TgGroupStatusMy,
  TgInlineQuery,
  TgInvoiceCheckout,
  TgInvoiceShipping,
  TgPhoto,
  TgText,
  TgUpdateType,
  TgUser
};

$_GET['a'] ??= '';
if(function_exists('Action_' . $_GET['a'])):
  call_user_func('Action_' . $_GET['a']);
endif;

function Action_():void{
  /**
   * @var TelegramBotLibrary $Bot
   */
  global $Bot, $Webhook;
  DebugTrace();
  $Webhook = $Bot->WebhookGet();
  if($Webhook === null):
    return;
  endif;

  if(get_class($Webhook) === TblCmd::class)://TblCmdEdited extends TblCmd
    Update_Cmd();
  elseif($Webhook instanceof TgCallback):
    Update_Callback();
  elseif($Webhook instanceof TgText):
    Update_Text();
  elseif($Webhook instanceof TgPhoto):
    Update_ListenerDual(StbDbListeners::Photo);
  elseif($Webhook instanceof TgInvoiceCheckout):
    Update_ListenerSimple(StbDbListeners::InvoiceCheckout);
  elseif($Webhook instanceof TgInvoiceShipping):
    Update_ListenerSimple(StbDbListeners::InvoiceShipping);
  elseif($Webhook instanceof TgInlineQuery):
    Update_ListenerSimple(StbDbListeners::InlineQuery);
  elseif($Webhook instanceof TgGroupStatusMy):
    Update_ListenerSimple(StbDbListeners::ChatMy);
  elseif($Webhook instanceof TgChatTitle):
    Update_ListenerSimple(StbDbListeners::Chat);
  endif;
}

function Action_WebhookSet():void{
  /**
   * @var TblData $BotData
   */
  global $BotData;
  $Webhook = new TblWebhook($BotData);
  try{
    $Webhook->Set(
      $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'],
      Updates: TgUpdateType::cases(),
      TokenWebhook: $BotData->TokenWebhook
    );
    echo '<p>Webhook set</p>';
    echo '<p><a href="index.php?a=WebhookGet">Click here see details</a></p>';
  }catch(TblException $e){
    echo '<p>Webhook fails</p>';
    echo '<p>' . $e->getMessage() . '</p>';
  }
}

function Action_WebhookGet():void{
  /**
   * @var TblData $BotData
   */
  global $BotData;
  $Webhook = new TblWebhook($BotData);
  $temp = $Webhook->Get();
  echo 'URL: ' . $temp['url'] . '<br>';
  echo 'Certificate: ' . ($temp['has_custom_certificate'] ? 'Yes' : 'No') . '<br>';
  echo 'Pending updates: ' . $temp['pending_update_count'] . '<br>';
  echo 'Max connections: ' . ($temp['max_connections'] ?? 0) . '<br>';
  echo 'Server: ' . ($temp['ip_address'] ?? 'None') . '<br>';
  echo 'Updates: ';
  if(isset($temp['allowed_updates'])):
    foreach($temp['allowed_updates'] as $update):
      echo $update . ', ';
    endforeach;
  else:
    echo 'None';
  endif;
  echo '<br>Last sync error: ';
  if(isset($temp['last_synchronization_error_date'])):
    echo date('Y-m-d H:i:s', $temp['last_synchronization_error_date']);
  else:
    echo 'Never';
  endif;
  echo '<br>Last error: ';
  if(isset($temp['last_error_date'])):
    echo date('Y-m-d H:i:s', $temp['last_error_date']) . ' - ';
    echo $temp['last_error_message'];
  else:
    echo 'None';
  endif;
}

function Action_WebhookDel():void{
  /**
  * @var TblData $BotData
  */
  global $BotData;
  $Webhook = new TblWebhook($BotData);
  try{
    $Webhook->Del();
  }catch(TblException $e){
    echo $e->getMessage();
  }
}

function Update_Callback():void{
  /**
   * @var TgCallback $Webhook
   * @var StbDatabase $Db
   * @var TelegramBotLibrary $bot
   * @var StbLanguageSys $Lang
   */
  global $Webhook, $Db, $Bot, $Lang;
  $Db->UserSeen($Webhook->User);
  if($Db->CallBackHashRun($Webhook->Callback) === false):
    $Bot->CallbackAnswer(
      $Webhook->Id,
      $Lang->Get('ButtonWithoutAction', Group: 'Errors')
    );
  endif;
}

function Update_Cmd():void{
  /**
   * @var TelegramBotLibrary $Bot
   * @var StbDatabase $Db
   * @var TblCmd $Webhook
   */
  global $Bot, $Db, $Webhook;
  $Db->UserSeen($Webhook->Data->User);

  //In a group, with many bots, the commands have the target bot.
  //This block check the target and caches the bot name
  if($Webhook->Data->Chat instanceof TgChat):
    $user = $Bot->MyGet();
    $user = $user->Nick;
    if($Webhook->Target !== null
    and $Webhook->Target !== $user):
      return;
    endif;
  endif;

  //Module command
  $module = $Db->Commands($Webhook->Command);
  if($module !== []):
    StbModuleTools::Load($module[0]['module']);
    call_user_func($module[0]['module'] . '::Command_' . $Webhook->Command);
    return;
  endif;

  if(StbBotTools::SendUserCmd($Webhook->Command) === false):
    StbBotTools::SendUserCmd('unknown');
  endif;
}

function Update_Text():void{
  /**
   * @var TgText $Webhook
   * @var StbDatabase $Db
   */
  global $Db, $Webhook;
  $Db->UserSeen($Webhook->Data->User);
  $Run = false;
  foreach($Db->ListenerGet(StbDbListeners::Text) as $listener):
    $Run = true;
    StbModuleTools::Load($listener['module']);
    if(call_user_func($listener['module'] . '::Listener_Text') === false):
      return;
    endif;
  endforeach;
  foreach($Db->ListenerGet(StbDbListeners::Text, $Webhook->Data->Chat->Id) as $listener):
    $Run = true;
    StbModuleTools::Load($listener['module']);
    if(call_user_func($listener['module'] . '::Listener_Text') === false):
      return;
    endif;
  endforeach;
  if($Run === false
  and $Webhook->Data->Chat instanceof TgUser):
    StbBotTools::SendUserCmd('dontknow', $Webhook->Text);
  endif;
  return;
}

function Update_ListenerDual(StbDbListeners $Listener):void{
  /**
   * @var TgPhoto $Webhook
   * @var StbDatabase $Db
   */
  global $Db, $Webhook;
  foreach($Db->ListenerGet($Listener) as $listener):
    StbModuleTools::Load($listener['module']);
    if(call_user_func($listener['module'] . '::Listener_' . $Listener->name) === false):
      return;
    endif;
  endforeach;
  foreach($Db->ListenerGet($Listener, $Webhook->Data->Chat->Id) as $listener):
    StbModuleTools::Load($listener['module']);
    if(call_user_func($listener['module'] . '::Listener_' . $Listener->name) === false):
      return;
    endif;
  endforeach;
}

function Update_ListenerSimple(StbDbListeners $Listener):void{
  /**
   * @var StbDatabase $Db
   */
  global $Db;
  foreach($Db->ListenerGet($Listener) as $listener):
    StbModuleTools::Load($listener['module']);
    if(call_user_func($listener['module'] . '::Listener_' . $Listener->name) === false):
      return;
    endif;
  endforeach;
}
<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.05.10.01

//This file are included by DirToken/index.php

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
  if(Debug & StbDebug::Bot):
    ob_start();
    var_dump($Webhook);
    LogBot(ob_get_contents());
    ob_end_flush();
  endif;

  if(get_class($Webhook) === 'TblCmd'):
    Update_Cmd();
  elseif(get_class($Webhook) === 'TgCallback'):
    Update_Callback();
  elseif(get_class($Webhook) === 'TgText'):
    Update_Text();
  elseif(get_class($Webhook) === 'TgInvoiceCheckout'):
    Update_InvoiceCheckout();
  elseif(get_class($Webhook) === 'TgInvoiceShipping'):
    Update_InvoiceShipping();
  elseif(get_class($Webhook) === 'TgInlineQuery'):
    Update_InlineQuery();
  elseif(get_class($Webhook) === 'TgGroupStatusMy'):
    Update_GroupStatusMy();
  endif;
}

function Action_WebhookSet():void{
  /**
   * @var TblData $BotData
   */
  global $BotData;
  $Webhook = new TblWebhook($BotData);
  $Webhook->Set($_SERVER['SCRIPT_URI'], Updates: TgUpdateType::cases());
  echo $Webhook->ErrorStr;
  echo '<p><a href="index.php?a=WebhookGet">Click here see details</a></p>';
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
  $Webhook->Del();
  echo $Webhook->ErrorStr;
}

function Update_Cmd():void{
  /**
   * @var TelegramBotLibrary $Bot
   * @var StbDatabaseSys $Db
   * @var TblCmd $Webhook
   */
  global $Bot, $Db, $Webhook, $UserLang;
  //When the sender is a chat/channel, they don't have the language
  $UserLang = $Webhook->Message->User->Language ?? DefaultLanguage;

  //In a group, with many bots, the commands have the target bot.
  //This block check the target and caches the bot name
  if($Webhook->Message->Chat->Type !== TgChatType::Private):
    $name = $Db->VariableGet(StbDbParam::UserDetails);
    if($name === null):
      $name = $Bot->MyGet();
      if($name !== null):
        $Db->VariableSet(StbDbParam::UserDetails, $name);
        $name = $name->Nick;
      endif;
    else:
      $name = $name['Nick'];
    endif;
    if($Webhook->Target !== null
    and $Webhook->Target !== $name):
      return;
    endif;
  endif;

  //Internal command
  $command = 'Command_' . strtolower($Webhook->Command);
  if(function_exists($command)):
    call_user_func($command);
    return;
  endif;

  //Module command
  $module = $Db->Commands($Webhook->Command);
  if($module !== null):
    call_user_func($module . '::Command_' . $Webhook->Command);
    return;
  endif;

  if(SendUserCmd($Webhook->Command) === false):
    SendUserCmd('unknown');
  endif;
}

function Update_Callback():void{
  /** @var TgCallback $Webhook */
  global $Webhook;
  if(function_exists('Callback_' . $Webhook->Data)):
    call_user_func('Callback_' . $Webhook->Data);
  endif;
}

function Update_Text():void{
  /**
   * @var TgText $Webhook
   * @var StbDatabaseSys $Db
   */
  global $Db, $Webhook;
  $Run = false;
  foreach($Db->ListenerGet(StbDbListeners::Text) as $listener):
    $Run = true;
    if(call_user_func($listener) === false):
      return;
    endif;
  endforeach;
  foreach($Db->ListenerGet(StbDbListeners::Text, $Webhook->Message->User->Id) as $listener):
    $Run = true;
    if(call_user_func($listener) === false):
      return;
    endif;
  endforeach;
  if($Run === false):
    SendUserCmd('dontknow');
  endif;
  return;
}

function Update_InvoiceCheckout():void{
  /**
   * @var StbDatabaseSys $Db
   */
  global $Db;
  foreach($Db->ListenerGet(StbDbListeners::InvoiceCheckout) as $listener):
    if(call_user_func($listener) === false):
      return;
    endif;
  endforeach;
}

function Update_InvoiceShipping():void{
  /**
   * @var StbDatabaseSys $Db
   */
  global $Db;
  foreach($Db->ListenerGet(StbDbListeners::InvoiceShipping) as $listener):
    if(call_user_func($listener) === false):
      return;
    endif;
  endforeach;
}

function Update_InlineQuery():void{
  /**
   * @var StbDatabaseSys $Db
   */
  global $Db;
  foreach($Db->ListenerGet(StbDbListeners::InlineQuery) as $listener):
    if(call_user_func($listener) === false):
      return;
    endif;
  endforeach;
}

function Update_GroupStatusMy():void{
  /**
   * @var StbDatabaseSys $Db
   */
  global $Db;
  foreach($Db->ListenerGet(StbDbListeners::ChatMy) as $listener):
    if(call_user_func($listener) === false):
      return;
    endif;
  endforeach;
}
<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.05.03.01

//This file are included by DirToken/index.php

$_GET['a'] ??= '';
if(function_exists('Action_' . $_GET['a'])):
  call_user_func('Action_' . $_GET['a']);
endif;

function Action_():void{
  /**
   * @var TelegramBotLibrary $Bot
   * @var StbDatabaseSys $Db
   * @var string $UserLang
   */
  global $Bot, $Webhook, $Db, $UserLang;
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
    /** @var TblCmd $Webhook */
    $UserLang = $Webhook->Message->User->Language;

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
      if($Webhook->Target !== $name):
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
    $commands = $Db->Commands();
    $module = $commands[$Webhook->Command] ?? null;
    if($module !== null):
      $command = $module . '::Command_' . $Webhook->Command;
      call_user_func($command);
      return;
    endif;

    if(SendUserCmd($Webhook->Command) === false):
      SendUserCmd('unknown');
    endif;
    return;
  endif;
  
  if(get_class($Webhook) === 'TgCallback'):
    /** @var TgCallback $Webhook */
    if(function_exists('Callback_' . $Webhook->Data)):
      call_user_func('Callback_' . $Webhook->Data);
    endif;
    return;
  endif;

  if(get_class($Webhook) === 'TgText'):
    /** @var TgText $Webhook */
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
  endif;

  if(get_class($Webhook) === 'TgInvoiceCheckout'):
    foreach($Db->ListenerGet(StbDbListeners::InvoiceCheckout) as $listener):
      if(call_user_func($listener) === false):
        return;
      endif;
    endforeach;
    return;
  endif;

  if(get_class($Webhook) === 'TgInvoiceShipping'):
    foreach($Db->ListenerGet(StbDbListeners::InvoiceShipping) as $listener):
      if(call_user_func($listener) === false):
        return;
      endif;
    endforeach;
    return;
  endif;

  if(get_class($Webhook) === 'TgInlineQuery'):
    foreach($Db->ListenerGet(StbDbListeners::InlineQuery) as $listener):
      if(call_user_func($listener) === false):
        return;
      endif;
    endforeach;
    return;
  endif;

  if(get_class($Webhook) === 'TgGroupStatusMy'):
    foreach($Db->ListenerGet(StbDbListeners::ChatMy) as $listener):
      if(call_user_func($listener) === false):
        return;
      endif;
    endforeach;
    return;
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
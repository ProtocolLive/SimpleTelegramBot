<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.03.16.00

require(dirname(__DIR__, 1) . '/system/system.php');

$_GET['a'] ??= '';
if(function_exists('Action_' . $_GET['a'])):
  call_user_func('Action_' . $_GET['a']);
endif;

function Action_():void{
  /**
   * @var TelegramBotLibrary $Bot
   * @var object $Webhook
   * @var StbDatabase $Db
   */
  global $Bot, $Webhook, $Db;
  DebugTrace();
  $Webhook = $Bot->WebhookGet();
  if(get_class($Webhook) === 'TblCmd'
  and function_exists('Command_' . strtolower($Webhook->Command))):
    call_user_func('Command_' . strtolower($Webhook->Command));
  elseif(get_class($Webhook) === 'TgCallback'):
    if(function_exists('Callback_' . $Webhook->Data)):
      call_user_func('Callback_' . $Webhook->Data);
      return;
    endif;
    $callbacks = $Db->Get(DbParam::Callbacks);
    if(array_search($Webhook->Data, $callbacks) !== false):
      //depois
    endif;
  elseif(get_class($Webhook) === 'TgText'):
    $listener = $Db->Get(DbParam::ListenerText, $Webhook->User->Id);
    if($listener !== null):
      call_user_func('Listener_' . $listener);
    endif;
  endif;
}

function Action_WebhookSet():void{
  /**
   * @var TblData $BotData
   */
  global $BotData;
  $Webhook = new TblWebhook($BotData);
  $Webhook->Set($_SERVER['SCRIPT_URI']);
  echo $Webhook->ErrorStr;
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
  echo 'Last error: ';
  if(isset($temp['last_error_date'])):
    echo date("Y-m-d H:i:s", $temp['last_error_date']) . ' - ';
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
<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.04.23.00

require(dirname(__DIR__, 1) . '/system/system.php');

$_GET['a'] ??= '';
if(function_exists('Action_' . $_GET['a'])):
  call_user_func('Action_' . $_GET['a']);
endif;

function Action_():void{
  /**
   * @var TelegramBotLibrary $Bot
   * @var StbSysDatabase $Db
   * @var string $UserLang
   */
  global $Bot, $Webhook, $Db, $UserLang;
  DebugTrace();
  $Webhook = $Bot->WebhookGet();
  if($Webhook === null):
    return;
  endif;
  ob_start();
  var_dump($Webhook);
  LogBot(ob_get_contents());
  ob_end_flush();

  if(get_class($Webhook) === 'TblCmd'):
    /** @var TblCmd $Webhook */
    $UserLang = $Webhook->User->Language;

    //In a group, with many bots, the commands have the target bot.
    //This block check the target and caches the bot name
    if($Webhook->Chat->Type !== TgChatType::Private):
      $name = $Db->Variable(StbSysDatabase::ParamUserDetails);
      if($name === null):
        $name = $Bot->MyGet();
        if($name !== null):
          $Db->Variable(StbSysDatabase::ParamUserDetails, $name);
          $name = $name->Nick;
        endif;
      else:
        $name = $name['Nick'];
      endif;
      if($Webhook->Target !== $name):
        return;
      endif;
    endif;

    $command = 'Command_' . strtolower($Webhook->Command);
    if(function_exists($command)):
      $command();
      return;
    endif;

    $commands = $Db->Commands();
    $module = $commands[$Webhook->Command] ?? null;
    if($module !== null):
      $command = $module . '::Command_' . $Webhook->Command;
      $command();
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
      return;
    endif;
    return;
  endif;

  if(get_class($Webhook) === 'TgText'):
    /** @var TgText $Webhook */
    $listener = $Db->Listener(StbDbListeners::Text, $Webhook->User->Id);
    if($listener !== null):
      call_user_func('Listener_' . $listener);
    endif;
  endif;

  if(get_class($Webhook) === 'TgInvoiceCheckout'):
    $listener = $Db->Listener(StbDbListeners::InvoiceCheckout);
    if($listener !== null):
      call_user_func($listener);
    endif;
  endif;

  if(get_class($Webhook) === 'TgInvoiceShipping'):
    $listener = $Db->Listener(StbDbListeners::InvoiceShipping);
    if($listener !== null):
      call_user_func($listener);
    endif;
  endif;

  return;
}

function Action_WebhookSet():void{
  /**
   * @var TblData $BotData
   */
  global $BotData;
  $Webhook = new TblWebhook($BotData);
  $Webhook->Set($_SERVER['SCRIPT_URI'], Updates: TgUpdateType::cases());
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
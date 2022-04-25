<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/FuncoesComuns
//2022.04.25.01

function LogEvent(string $Event, string $Additional = null):void{
  /** @var TblCmd $Webhook */
  global $Webhook;
  DebugTrace();
  $temp = date('Y-m-d H:i:s') . "\t";
  if($Webhook->Message->User->Nick !== null):
    $temp .= '@' . $Webhook->Message->User->Nick . ' ';
  endif;
  $temp .= '(' . $Webhook->Message->User->Id . ') ';
  $temp .= $Webhook->Message->User->Name;
  if($Webhook->Message->User->NameLast !== null):
    $temp .= ' ' . $Webhook->Message->User->NameLast;
  endif;
  $temp .= "\t";
  $temp .= $Event;
  if($Additional !== null):
    $temp .= "\t" . $Additional;
  endif;
  $temp .= "\n";
  file_put_contents(DirToken . '/logs/usage.log', $temp);
}

function LogBot(string $Msg):void{
  DebugTrace();
  $Msg = date('Y-m-d H:i:s') . "\n" . $Msg . "\n";
  file_put_contents(DirToken . '/logs/bot.log', $Msg, FILE_APPEND);
}

function SendUserCmd(string $Command, string $EventAdditional = null):bool{
  /**
   * @var TelegramBotLibrary $Bot
   * @var TgCmd $Webhook
   * @var string $UserLang
   */
  global $Bot, $Webhook, $UserLang;
  DebugTrace();
  $Photo = false;
  $Text = false;
  $File = DirSystem . "/UserCmds/$UserLang/$Command";
  
  $temp = $File . 'jpg';
  if(is_file($temp)):
    $Bot->PhotoSend(
      $Webhook->Message->Chat->Id,
      $temp
    );
    $Photo = true;
  endif;

  $File .= '.txt';
  if(is_file($File)):
    $text = file_get_contents($File);
    $text = str_replace('##NAME##', $Webhook->Message->User->Name, $text);
    $text = explode('##BREAK##', $text);
    foreach($text as $txt):
      $Bot->TextSend(
        $Webhook->Message->Chat->Id,
        $txt
      );
    endforeach;
    $Text = true;
  endif;

  if($Photo or $Text):
    LogEvent($Command, $EventAdditional);
    return true;
  else:
    return false;
  endif;
}
<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/FuncoesComuns
//2022.04.30.02

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
  file_put_contents(DirLogs . '/usage.log', $temp, FILE_APPEND);
}

function LogBot(string $Msg):void{
  DebugTrace();
  $Msg = date('Y-m-d H:i:s') . "\n" . $Msg . "\n\n";
  file_put_contents(DirLogs . '/bot.log', $Msg, FILE_APPEND);
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
  $File = DirUserCmds . '/' . $UserLang . '/' . $Command;
  
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
        $txt,
        TgParseMode::Html
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

function UpdateCheck():array{
  $NowHashes = HashDir('sha1', DirSystem);
  $ServerHashes = file_get_contents('https://raw.githubusercontent.com/ProtocolLive/SimpleTelegramBot/main/src.sha1');
  $ServerHashes = explode("\n", $ServerHashes);
  array_pop($ServerHashes);
  foreach($ServerHashes as $sh):
    $sh = explode('  ', $sh);
    $file = str_replace('src/', DirSystem . '/', $sh[1]);
    if(strpos($file, DirSystem . '/config.php') !== false):
      continue;
    endif;
    if(isset($NowHashes[$file])
    and $sh[0] !== $NowHashes[$file]):
      $return[] = $file;
    endif;
  endforeach;
  $ServerHashes = file_get_contents('https://raw.githubusercontent.com/ProtocolLive/TelegramBotLibrary/main/src.sha1');
  $ServerHashes = explode("\n", $ServerHashes);
  array_pop($ServerHashes);
  foreach($ServerHashes as $sh):
    $sh = explode('  ', $sh);
    $file = str_replace('src/', DirSystem . '/class/', $sh[1]);
    if(isset($NowHashes[$file])
    and $sh[0] !== $NowHashes[$file]):
      $return[] = $file;
    endif;
  endforeach;
  return $return;
}
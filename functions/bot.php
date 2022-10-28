<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/FuncoesComuns
//2022.10.28.02

use ProtocolLive\SimpleTelegramBot\StbObjects\StbDbAdminData;
use ProtocolLive\SimpleTelegramBot\StbObjects\StbDbAdminPerm;
use ProtocolLive\SimpleTelegramBot\StbObjects\StbLog;
use ProtocolLive\TelegramBotLibrary\TgObjects\TgChat;
use ProtocolLive\TelegramBotLibrary\TgObjects\TgChatType;
use ProtocolLive\TelegramBotLibrary\TgObjects\TgParseMode;
use ProtocolLive\TelegramBotLibrary\TgObjects\TgUser;

function StbLog(
  int $Type,
  string $Msg,
  bool $NewLine = true
):void{
  /**
   * @var TblData TblBotData
   */
  global $BotData;
  DebugTrace();
  if(($BotData->Log & $Type) === false):
    return;
  endif;
  $Msg = date('Y-m-d H:i:s') . PHP_EOL . $Msg . PHP_EOL;
  if($NewLine):
    $Msg .= PHP_EOL;
  endif;
  if($Type === StbLog::Cron):
    $file = 'cron';
  endif;
  file_put_contents(DirLogs . '/' . $file . '.log', $Msg, FILE_APPEND);
}

function SendUserCmd(string $Command, string $EventAdditional = null):bool{
  /**
   * @var TelegramBotLibrary $Bot
   * @var TgCmd $Webhook
   * @var string $UserLang
   * @var StbDatabase $Db
   */
  global $Bot, $Webhook, $Db;
  DebugTrace();
  $Photo = false;
  $Text = false;
  $data = $Db->UserGet($Webhook->Message->User->Id);
  $File = DirUserCmds . '/' . ($data->Language ?? DefaultLanguage) . '/' . $Command;
  
  foreach(['jpg', 'png', 'gif'] as $ext):
    $temp = $File . '.' . $ext;
    if(is_file($temp)):
      $Bot->PhotoSend(
        $Webhook->Message->Chat->Id,
        $temp
      );
      $Photo = true;
      break;
    endif;
  endforeach;

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
    $Db->UsageLog($Webhook->Message->Chat->Id, $Command, $EventAdditional);
    return true;
  else:
    return false;
  endif;
}

function UpdateCheck():array{
  $NowHashes = HashDir('sha1', DirSystem);
  $ServerHashes = file_get_contents('https://raw.githubusercontent.com/ProtocolLive/SimpleTelegramBot/main/src.sha1');
  $ServerHashes = explode(PHP_EOL, $ServerHashes);
  array_pop($ServerHashes);
  foreach($ServerHashes as $sh):
    $sh = explode('  ', $sh);
    $file = str_replace('src/', DirSystem . '/', $sh[1]);
    if(strpos($file, DirSystem . '/config.php') !== false):
      continue;
    endif;
    if(isset($NowHashes[$file])
    and $sh[0] !== $NowHashes[$file]):
      $return[] = $sh[1];
    endif;
  endforeach;
  $ServerHashes = file_get_contents('https://raw.githubusercontent.com/ProtocolLive/TelegramBotLibrary/main/src.sha1');
  $ServerHashes = explode(PHP_EOL, $ServerHashes);
  array_pop($ServerHashes);
  foreach($ServerHashes as $sh):
    $sh = explode('  ', $sh);
    $file = str_replace('src/', DirSystem . '/vendor/protocollive/telegrambotlibrary/src/', $sh[1]);
    if(isset($NowHashes[$file])
    and $sh[0] !== $NowHashes[$file]):
      $return[] = $sh[1];
    endif;
  endforeach;
  return $return;
}

/**
 * @throws TypeError
 */
function Tgchat2Tguser(TgChat $Chat):TgUser{
  if($Chat->Type !== TgChatType::Private):
    throw new TypeError('Chat type must be of type Private');
  endif;
  return new TgUser([
    'id' => $Chat->Id,
    'first_name' => $Chat->Name,
    'last_name' => $Chat->NameLast,
    'username' => $Chat->Nick
  ]);
}

function AdminCheck(
  int $Id,
  StbDbAdminPerm $Level = StbDbAdminPerm::All,
  bool $SendDenied = true
):StbDbAdminData|null{
  /**
   * @var StbDatabase $Db
   * @var TelegramBotLibrary $Bot
   * @var StbLanguageSys $Lang
   */
  global $Db, $Bot, $Lang;
  $user = $Db->Admin($Id);
  if($user === false
  or ($user->Perms->value & $Level->value) === false):
    if($SendDenied):
      $Bot->TextSend(
        $Id,
        $Lang->Get('Denied', Group: 'Errors')
      );
    endif;
    return null;
  else:
    return $user;
  endif;
}
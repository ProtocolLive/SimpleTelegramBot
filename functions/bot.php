<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/FuncoesComuns
//2022.12.30.01

use ProtocolLive\SimpleTelegramBot\StbObjects\{
  StbDbAdminData,
  StbDbAdminPerm,
  StbLog
};
use ProtocolLive\TelegramBotLibrary\TgObjects\{
  TgChat,
  TgParseMode,
  TgUser
};

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

function SendUserCmd(
  string $Command,
  string $EventAdditional = null
):bool{
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
  $data = $Db->UserGet($Webhook->Data->User->Id);
  $File = DirUserCmds . '/' . ($data->Language ?? DefaultLanguage) . '/' . $Command;
  
  foreach(['jpg', 'png', 'gif'] as $ext):
    $temp = $File . '.' . $ext;
    if(is_file($temp)):
      $Bot->PhotoSend(
        $Webhook->Data->Chat->Id,
        $temp
      );
      $Photo = true;
      break;
    endif;
  endforeach;

  $File .= '.txt';
  if(is_file($File)):
    $text = file_get_contents($File);
    $text = str_replace('##NAME##', $Webhook->Data->User->Name, $text);
    $text = explode('##BREAK##', $text);
    foreach($text as $txt):
      $Bot->TextSend(
        $Webhook->Data->Chat->Id,
        $txt,
        ParseMode: TgParseMode::Html
      );
    endforeach;
    $Text = true;
  endif;

  if($Photo or $Text):
    $Db->UsageLog($Webhook->Data->Chat->Id, $Command, $EventAdditional);
    return true;
  else:
    return false;
  endif;
}

/**
 * @throws TypeError
 */
function Tgchat2Tguser(TgChat $Chat):TgUser{
  return new TgUser([
    'id' => $Chat->Id,
    'first_name' => $Chat->Name,
    'last_name' => $Chat->NameLast,
    'username' => $Chat->Nick
  ]);
}
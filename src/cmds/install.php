<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.03.24.01

function Command_installmod():void{
  /**
   * @var TelegramBotLibrary $Bot
   * @var StbSysDatabase $Db
   * @var StbLanguage $Lang
   * @var TblCmd $Webhook
   */
  global $Bot, $Db, $Lang, $Webhook;
  DebugTrace();
  if($Webhook->Message->User->Id !== Admin):
    $Bot->TextSend(
      $Webhook->User->Id,
      $Lang->Get('Denied')
    );
    return;
  endif;

  $ModulesFiles = [];
  foreach(glob(DirSystem . '/modules/*', GLOB_ONLYDIR) as $file):
    $ModulesFiles[] = basename($file);
  endforeach;
  foreach($Db->Modules() as $mod):
    $temp = array_search($mod, $ModulesFiles);
    if($temp !== null):
      unset($ModulesFiles[$temp]);
    endif;
  endforeach;

  if(count($ModulesFiles) === 0):
    $Bot->TextSend(
      $Webhook->User->Id,
      $Lang->Get('InstallNone', null, 'Module')
    );
    return;
  endif;

  $mk = new TblMarkupInline;
  $line = 0;
  $col = 0;
  foreach($ModulesFiles as $mod):
    $mk->ButtonCallback($line, $col++, $mod, 'InsModPic ' . $mod);
    if($col === 2):
      $line++;
      $col = 0;
    endif;
  endforeach;

  $Bot->TextSend(
    $Webhook->Message->User->Id,
    $Lang->Get('InstallPick', null, 'Module'),
    Markup: $mk
  );
}

function Callback_InsModPic():void{
  /**
   * @var TelegramBotLibrary $Bot
   * @var TgCallback $Webhook
   * @var StbLanguage $Lang
   * @var StbSysDatabase $Db
   */
  global $Bot, $Webhook, $Lang, $Db;
  DebugTrace();
  $module = $Webhook->Parameter;

  require(DirSystem . '/modules/' . $module . '/index.php');
  if(method_exists($module, 'Install') === false):
    $Bot->TextSend(
      $Webhook->User->Id,
      sprintf(
        $Lang->Get('InstallNotFound', null, 'Module'),
        $module
      )
    );
    return;
  endif;

  /** @var InterfaceModule $module */
  $module::Install($Bot, $Webhook, $Db, $Lang);
}

function Command_uninstallmod():void{
  /**
   * @var TelegramBotLibrary $Bot
   * @var StbSysDatabase $Db
   * @var StbLanguage $Lang
   * @var TblCmd $Webhook
   */
  global $Bot, $Db, $Lang, $Webhook;
  DebugTrace();
  if($Webhook->User->Id !== Admin):
    $Bot->TextSend(
      $Webhook->User->Id,
      $Lang->Get('Denied')
    );
    return;
  endif;

  $mods = $Db->Modules();
  if(count($mods) === 0):
    $Bot->TextSend(
      $Webhook->User->Id,
      $Lang->Get('UnInstallNone', null, 'Module')
    );
    return;
  endif;

  $mk = new TblMarkupInline;
  $line = 0;
  $col = 0;
  foreach($mods as $mod => $date):
    $mk->ButtonCallback($line, $col++, $mod, 'UniModPic ' . $mod);
    if($col === 2):
      $line++;
      $col = 0;
    endif;
  endforeach;
  $Bot->TextSend(
    $Webhook->User->Id,
    $Lang->Get('UnInstallPick', null, 'Module'),
    Markup: $mk
  );
}

function Callback_UniModPic():void{
  /**
   * @var TelegramBotLibrary $Bot
   * @var TgCallback $Webhook
   * @var StbLanguage $Lang
   * @var StbSysDatabase $Db
   */
  global $Bot, $Webhook, $Lang, $Db;
  DebugTrace();
  $module = $Webhook->Parameter;

  if(method_exists($module, 'Uninstall') === false):
    $Bot->TextSend(
      $Webhook->User->Id,
      sprintf(
        $Lang->Get('UnInstallNotFound', null, 'Module'),
        $module
      )
    );
    return;
  endif;

  /** @var InterfaceModule $module */
  $module::Uninstall($Bot, $Webhook, $Db, $Lang);
}
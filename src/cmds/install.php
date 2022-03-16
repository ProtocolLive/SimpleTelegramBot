<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.03.16.00

function Command_installmod():void{
  /**
   * @var TelegramBotLibrary $Bot
   * @var StbSysDatabase $Db
   * @var StbLanguage $Lang
   * @var TblCmd $Webhook
   */
  global $Bot, $Db, $Lang, $Webhook;
  DebugTrace();
  if($Webhook->User->Id !== Admin):
    $Bot->SendText(
      $Webhook->User->Id,
      $Lang->Get('Denied')
    );
    return;
  endif;
  $ModulesFiles = array_map(function($var){
    return basename($var);
  }, glob(DirSystem . '/modules/*', GLOB_ONLYDIR));
  foreach($Db->Modules() as $mod):
    $temp = array_search($mod, $ModulesFiles);
    if($temp !== null):
      unset($ModulesFiles[$temp]);
    endif;
  endforeach;

  if(count($ModulesFiles) === 0):
    $Bot->SendText(
      $Webhook->User->Id,
      $Lang->Get('ModuleInstallNone')
    );
    return;
  endif;

  $mk = new TblMarkupInline;
  $line = 0;
  $col = 0;
  foreach($ModulesFiles as $mod):
    $mk->ButtonCallback($line, $col++, $mod, 'ModulePick ' . $mod);
    if($col === 2):
      $line++;
      $col = 0;
    endif;
  endforeach;

  $Bot->SendText(
    $Webhook->User->Id,
    $Lang->Get('ModuleInstallPick'),
    Markup: $mk
  );
}

function Callback_ModulePick():void{
  /**
   * @var TelegramBotLibrary $Bot
   * @var TgCallback $Webhook
   * @var StbLanguage $Lang
   * @var StbSysDatabase $Db
   */
  global $Bot, $Webhook, $Lang, $Db;
  DebugTrace();
  $module = $Webhook->Parameter;

  $modules = $Db->Modules();
  if(isset($modules[$module])):
    $Bot->SendText(
      $Webhook->User->Id,
      sprintf(
        $Lang->Get('ModuleInstallAlready'),
        $module
      )
    );
    return;
  endif;

  $file = DirSystem . '/modules/' . $module . '/index.php';
  if(is_file($file) === false):
    $Bot->SendText(
      $Webhook->User->Id,
      sprintf(
        $Lang->Get('ModuleNotFound'),
        $module
      )
    );
    return;
  endif;

  require($file);
  if(method_exists($module, 'Install') === false):
    $Bot->SendText(
      $Webhook->User->Id,
      sprintf(
        $Lang->Get('ModuleInstallNotFound'),
        $module
      )
    );
    return;
  endif;

  /** @var InterfaceModule $module */
  $module::Install($Bot, $Webhook, $Db, $Lang);
}
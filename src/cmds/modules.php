<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.04.30.00

function Callback_Modules():void{
  /**
   * @var TelegramBotLibrary $Bot
   * @var StbDatabaseSys $Db
   * @var StbLanguageSys $Lang
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
  $mk = new TblMarkupInline;
  $line = 0;
  $col = 0;
  $mk->ButtonCallback(
    $line,
    $col++,
    $Lang->Get('Back'),
    'Admin'
  );
  $mk->ButtonCallback(
    $line,
    $col++,
    $Lang->Get('Add'),
    'ModuleAdd'
  );

  foreach($mods as $mod => $time):
    $mk->ButtonCallback(
      $line,
      $col++,
      $mod,
      'Mod ' . $mod
    );
    if($col === 4):
      $col = 0;
      $line++;
    endif;
  endforeach;

  $Bot->TextEdit(
    Admin,
    $Webhook->Message->Id,
    $Lang->Get('Modules', Group: 'Module'),
    Markup: $mk
  );
}

function Callback_ModuleAdd():void{
  /**
   * @var TelegramBotLibrary $Bot
   * @var StbDatabaseSys $Db
   * @var StbLanguageSys $Lang
   * @var TgCallback $Webhook
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

  $mk = new TblMarkupInline;
  $line = 0;
  $col = 0;

  $ModulesFiles = [];
  foreach(glob(DirModules . '/*', GLOB_ONLYDIR) as $file):
    $ModulesFiles[] = basename($file);
  endforeach;
  foreach($Db->Modules() as $mod):
    $temp = array_search($mod, $ModulesFiles);
    if($temp !== null):
      unset($ModulesFiles[$temp]);
    endif;
  endforeach;

  $mk->ButtonCallback(
    $line,
    $col++,
    $Lang->Get('Back'),
    'Modules'
  );
  foreach($ModulesFiles as $mod):
    $mk->ButtonCallback(
      $line,
      $col++,
      $mod,
      'InsModPic ' . $mod
    );
    if($col === 4):
      $line++;
      $col = 0;
    endif;
  endforeach;

  $Bot->TextEdit(
    Admin,
    $Webhook->Message->Id,
    $Lang->Get('InstallPick', Group: 'Module'),
    Markup: $mk
  );
}

function Callback_InsModPic():void{
  /**
   * @var TelegramBotLibrary $Bot
   * @var TgCallback $Webhook
   * @var StbLanguageSys $Lang
   * @var StbDatabaseSys $Db
   */
  global $Bot, $Webhook, $Lang, $Db;
  DebugTrace();
  $module = $Webhook->Parameter;

  $mk = new TblMarkupInline;
  $line = 0;
  $col = 0;

  require(DirModules . '/' . $module . '/index.php');
  if(method_exists($module, 'Install') === false):
    $mk->ButtonCallback(
      $line,
      $col++,
      $Lang->Get('Back'),
      'ModuleAdd'
    );
    $Bot->TextEdit(
      Admin,
      $Webhook->Message->Id,
      $Lang->Get('InstallNotFound', null, 'Module'),
      Markup: $mk
    );
    return;
  endif;
  if(method_exists($module, 'Uninstall') === false):
    $mk->ButtonCallback(
      $line,
      $col++,
      $Lang->Get('Back'),
      'ModuleAdd'
    );
    $Bot->TextEdit(
      Admin,
      $Webhook->Message->Id,
      $Lang->Get('UninstallNotFound', null, 'Module'),
      Markup: $mk
    );
    return;
  endif;
  call_user_func($module . '::Install', $Bot, $Webhook, $Db);
}

function Callback_Mod():void{
  /**
   * @var TelegramBotLibrary $Bot
   * @var StbDatabaseSys $Db
   * @var StbLanguageSys $Lang
   * @var TgCallback $Webhook
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

  $mk = new TblMarkupInline;
  $line = 0;
  $col = 0;
  $date = $Db->Modules($Webhook->Parameter);
  $mk->ButtonCallback(
    $line,
    $col++,
    $Lang->Get('Back'),
    'Modules'
  );
  $mk->ButtonCallback(
    $line,
    $col++,
    $Lang->Get('UninstallButton', Group: 'Module'),
    'UniModPic1 ' . $Webhook->Parameter
  );
  $Bot->TextEdit(
    Admin,
    $Webhook->Message->Id,
    sprintf(
      $Lang->Get('Module', Group: 'Module'),
      $Webhook->Parameter,
      date('Y-m-d H:i:s', $date)
    ),
    Markup: $mk
  );
}

function Callback_UniModPic1():void{
  /**
   * @var TelegramBotLibrary $Bot
   * @var StbLanguageSys $Lang
   * @var TgCallback $Webhook
   */
  global $Bot, $Lang, $Webhook;
  DebugTrace();
  if($Webhook->User->Id !== Admin):
    $Bot->TextSend(
      $Webhook->User->Id,
      $Lang->Get('Denied')
    );
    return;
  endif;
  $mk = new TblMarkupInline;
  $mk->ButtonCallback(
    0,
    0,
    $Lang->Get('Back'),
    'Module ' . $Webhook->Parameter
  );
  $mk->ButtonCallback(
    0,
    1,
    $Lang->Get('Yes'),
    'UniModPic2 ' . $Webhook->Parameter
  );
  $Bot->MarkupEdit(
    Admin,
    $Webhook->Message->Id,
    Markup: $mk
  );
}

function Callback_UniModPic2():void{
  /**
   * @var TelegramBotLibrary $Bot
   * @var TgCallback $Webhook
   * @var StbDatabaseSys $Db
   */
  global $Bot, $Webhook, $Db;
  DebugTrace();
  call_user_func($Webhook->Parameter . '::Uninstall', $Bot, $Webhook, $Db);
}
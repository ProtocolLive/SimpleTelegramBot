<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/FuncoesComuns
//2022.04.20.01

enum StbDbListeners:string{
  case Text = 'Text';
  case InlineQuery = 'InlineQuery';
  case Invoice = 'Invoice';
}

class StbSysDatabase{
  private readonly string $DirToken;

  private const ParamCommands = 'Commands';
  private const ParamModules = 'Modules';
  private const ParamVariables = 'Variables';

  private function Open(int $User = null):array{
    DebugTrace();
    if($User === null):
      $file = $this->DirToken . '/db/system.json';
    else:
      $file = $this->DirToken . '/db/' . $User . '.json';
    endif;
    if(is_file($file)):
      $db = file_get_contents($file);
      $db = json_decode($db, true);
    else:
      $db = [];
    endif;
    return $db;
  }

  private function Save(array $Db, int $User = null):void{
    DebugTrace();
    if($User === null):
      $file = $this->DirToken . '/db/system.json';
    else:
      $file = $this->DirToken . '/db/' . $User . '.json';
    endif;
    $db = json_encode($Db);
    DirCreate(dirname($file));
    file_put_contents($file, $db);
  }

  public function __construct(string $DirToken){
    DebugTrace();
    $this->DirToken = $DirToken;
  }

  public function ModuleInstall(string $Module):void{
    DebugTrace();
    $db = $this->Open();
    $db['System'][self::ParamModules][$Module] = time();
    $this->Save($db);
  }

  public function ModuleUninstall(string $Module):void{
    DebugTrace();
    $db = $this->Open();
    unset($db['System'][self::ParamModules][$Module]);
    unset($db[$Module]);
    $this->Save($db);
  }

  /**
   * List all installed modules or check if a module are installed
   * @param string $Module
   * @return array|bool
   */
  public function Modules(string $Module = null):array|bool{
    DebugTrace();
    $db = $this->Open();
    $db['System'][self::ParamModules] ??= [];
    if($Module === null):
      return $db['System'][self::ParamModules];
    else:
      return isset($db['System'][self::ParamModules][$Module]);
    endif;
  }

  public function CommandAdd(string $Command, string $Module):void{
    DebugTrace();
    $db = $this->Open();
    $db['System'][self::ParamCommands][$Command] = $Module;
    $this->Save($db);
  }

  public function CommandDel(string $Command):void{
    DebugTrace();
    $db = $this->Open();
    unset($db['System'][self::ParamCommands][$Command]);
    $this->Save($db);
  }

  /**
   * List all commands or check if a commands exists
   * @param string $Command
   * @return array|string|null Return all commands, the respective module or null for command not found
   */
  public function Commands(string $Command = null):array|string|null{
    DebugTrace();
    $db = $this->Open();
    $db['System'][self::ParamCommands] ??= [];
    if($Command === null):
      return $db['System'][self::ParamCommands];
    else:
      return $db['System'][self::ParamCommands][$Command] ?? null;
    endif;
  }

  /**
   * List a module commands
   * @param string $Module
   * @return array
   */
  public function ModuleCommands(string $Module):array{
    DebugTrace();
    $db = $this->Open();
    $db['System'][self::ParamCommands] ??= [];
    return array_keys($db['System'][self::ParamCommands], $Module);
  }

  public function ListenerAdd(
    StbDbListeners $Listener,
    string $Function,
    int $User
  ):void{
    DebugTrace();
    if($Listener === StbDbListeners::InlineQuery):
      $User = null;
    endif;
    $db = $this->Open($User);
    $db['System']['Listeners'][$Listener->value] = $Function;
    $this->Save($db);
  }

  public function ListenerDel(
    StbDbListeners $Listener,
    int $User
  ):void{
    DebugTrace();
    if($Listener === StbDbListeners::InlineQuery):
      $User = null;
    endif;
    $db = $this->Open($User);
    unset($db['System']['Listeners'][$Listener->value]);
    $this->Save($db);
  }

  public function Listener(
    StbDbListeners $Listener,
    int $User
  ):string|null{
    DebugTrace();
    if($Listener === StbDbListeners::InlineQuery):
      $User = null;
    endif;
    $db = $this->Open($User);
    return $db['System']['Listeners'][$Listener->value] ?? null;
  }

  public function Variable(string $Name, string $Value = null):string|bool|null{
    DebugTrace();
    $db = $this->Open();
    if($Value === null):
      return $db['System'][self::ParamVariables][$Name] ?? null;
    else:
      $db['System'][self::ParamVariables][$Name] = $Value;
      $this->Save($db);
      return true;
    endif;
  }
}

class StbDatabase{
  private readonly string $DirToken;
  private readonly string $Module;

  private function OpenAll(int $User = null):array{
    DebugTrace();
    if($User === null):
      $file = $this->DirToken . '/db/system.json';
    else:
      $file = $this->DirToken . '/db/' . $User . '.json';
    endif;
    if(is_file($file)):
      $db = file_get_contents($file);
      $db = json_decode($db, true);
    else:
      $db = [];
    endif;
    return $db;
  }

  private function Open(int $User = null):array{
    DebugTrace();
    $db = $this->OpenAll($User);
    return $db[$this->Module] ?? [];
  }

  private function Save(array $Db, int $User = null):void{
    DebugTrace();
    if($User === null):
      $file = $this->DirToken . '/db/system.json';
    else:
      $file = $this->DirToken . '/db/' . $User . '.json';
    endif;
    $db = $this->OpenAll($User);
    $db[$this->Module] = $Db;
    $db = json_encode($db);
    DirCreate(dirname($file));
    file_put_contents($file, $db);
  }

  public function __construct(string $DirToken, string $Module){
    DebugTrace();
    if($Module === 'System'):
      $Module .= 1;
    endif;
    $this->DirToken = $DirToken;
    $this->Module = $Module;
  }

  public function Set(string $Name, mixed $Value, int $User = null){
    DebugTrace();
    $db = $this->Open($User);
    $db[$Name] = $Value;
    $this->Save($db, $User);
  }

  public function Get(string $Name, int $User = null):mixed{
    DebugTrace();
    $db = $this->Open($User);
    return $db[$Name] ?? null;
  }

  public function Del(string $Name, int $User = null):void{
    DebugTrace();
    $db = $this->Open($User);
    unset($db[$Name]);
    $this->Save($db, $User);
  }
}
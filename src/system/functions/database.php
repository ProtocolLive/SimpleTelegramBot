<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/FuncoesComuns
//2022.03.17.03

class StbSysDatabase{
  private readonly string $DirToken;

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
    return $db['System'] ?? [];
  }

  private function Save(array $Db, int $User = null):void{
    DebugTrace();
    if($User === null):
      $file = $this->DirToken . '/db/system.json';
    else:
      $file = $this->DirToken . '/db/' . $User . '.json';
    endif;
    $db['System'] = $Db;
    $db = json_encode($db);
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
    $db[DbParam::Modules][$Module] = time();
    $this->Save($db);
  }

  public function ModuleUninstall(string $Module):void{
    $db = $this->Open();
    unset($db[DbParam::Modules][$Module]);
    $this->Save($db);
  }

  /**
   * List all installed modules or check if a module are installed
   * @param string $Module
   * @return array|bool
   */
  public function Modules(string $Module = null):array|bool{
    $db = $this->Open();
    $db[DbParam::Modules] ??= [];
    if($Module === null):
      return $db[DbParam::Modules];
    else:
      return isset($db[DbParam::Modules][$Module]);
    endif;
  }

  public function CommandAdd(string $Command, string $Module):void{
    $db = $this->Open();
    $db[DbParam::Commands][$Command] = $Module;
    $this->Save($db);
  }

  public function CommandDel(string $Command):void{
    $db = $this->Open();
    unset($db[DbParam::Commands][$Command]);
    $this->Save($db);
  }

  /**
   * List all commands or check if a commands exists
   * @param string $Command
   * @return array|string|null Return all commands, the respective module or null for command not found
   */
  public function Commands(string $Command = null):array|string|null{
    $db = $this->Open();
    $db[DbParam::Commands] ??= [];
    if($Command === null):
      return $db[DbParam::Commands];
    else:
      return $db[DbParam::Commands][$Command] ?? null;
    endif;
  }

  /**
   * List a module commands
   * @param string $Module
   * @return array
   */
  public function ModuleCommands(string $Module):array{
    $db = $this->Open();
    $db[DbParam::Commands] ??= [];
    return array_keys($db[DbParam::Commands], $Module);
  }

  public function ListenerTextAdd(int $User, string $Listener):void{
    DebugTrace();
    $db = $this->Open($User);
    $db[DbParam::ListenerText] = $Listener;
    $this->Save($db);
  }

  public function ListenerTextDel(int $User):void{
    DebugTrace();
    $db = $this->Open($User);
    unset($db[DbParam::ListenerText]);
    $this->Save($db);
  }

  public function ListenerText(int $User):string|null{
    DebugTrace();
    $db = $this->Open($User);
    return $db[DbParam::ListenerText] ?? null;
  }
}

class StbDatabase{
  private readonly string $DirToken;
  private readonly string $Module;

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
    return $db[$this->Module] ?? [];
  }

  private function Save(array $Db, int $User = null):void{
    DebugTrace();
    if($User === null):
      $file = $this->DirToken . '/db/system.json';
    else:
      $file = $this->DirToken . '/db/' . $User . '.json';
    endif;
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
<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/FuncoesComuns
//2022.05.14.00

enum StbDbListeners{
  case ChatMy;
  case Document;
  case Text;
  case InlineQuery;
  case Invoice;
  case InvoiceCheckout;
  case InvoiceShipping;
  case Photo;
  case Voice;
}

abstract class StbDbAdminData{
  public const Creation = 0;
  public const Perm = 1;
}

abstract class StbDbAdminPerm{
  public const All = -1;
  public const Admins = 1;
  public const Modules = 2;
  public const UserCmds = 4;
  public const Stats = 8;
}

abstract class StbDbParam{
  public const UserDetails = 'UserDetails';
}

class StbDatabaseSys{
  private const ParamAdmins = 'Admins';
  private const ParamCommands = 'Commands';
  private const ParamModules = 'Modules';
  private const ParamVariables = 'Variables';
  private const ParamListeners = 'Listeners';

  private function Open(int $User = null):array{
    DebugTrace();
    if($User === null):
      $file = DirDb . '/system.json';
    else:
      $file = DirDb . '/' . $User . '.json';
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
      $file = DirDb . '/system.json';
    else:
      $file = DirDb . '/' . $User . '.json';
    endif;
    $db = json_encode($Db);
    DirCreate(dirname($file));
    file_put_contents($file, $db);
  }

  private function NoUserListener(
    StbDbListeners $Listener
  ):bool{
    if($Listener === StbDbListeners::InlineQuery
    or $Listener === StbDbListeners::ChatMy):
      return true;
    else:
      return false;
    endif;
  }

  private function AdminSuper(&$Var):void{
    $Var = [Admin => [
      StbDbAdminData::Creation => 0,
      StbDbAdminData::Perm => StbDbAdminPerm::All]
    ] + ($Var ?? []);
  }

  public function Admin(
    int $User
  ):array|false{
    DebugTrace();
    $db = $this->Open();
    $this->AdminSuper($db['System'][self::ParamAdmins]);
    return $db['System'][self::ParamAdmins][$User] ?? false;
  }

  public function AdminAdd(
    int $User,
    int $Perms
  ):bool{
    DebugTrace();
    if($User === Admin):
      return false;
    endif;
    $db = $this->Open();
    if(isset($db['System'][self::ParamAdmins][$User]) === false):
      $db['System'][self::ParamAdmins][$User] = [
        StbDbAdminData::Creation => time(),
        StbDbAdminData::Perm => $Perms
      ];
      $this->Save($db);
      return true;
    endif;
    return false;
  }

  public function AdminDel(
    int $User
  ):bool{
    DebugTrace();
    if($User === Admin):
      return false;
    endif;
    $db = $this->Open();
    unset($db['System'][self::ParamAdmins][$User]);
    $this->Save($db);
    return true;
  }

  public function AdminEdit(
    int $User,
    int $Perm
  ):bool{
    DebugTrace();
    if($User === Admin):
      return false;
    endif;
    $db = $this->Open();
    return $db['System'][self::ParamAdmins][$User][StbDbAdminData::Perm] = $Perm;
    $this->Save($db);
    return true;
  }

  public function Admins():array{
    DebugTrace();
    $db = $this->Open();
    $this->AdminSuper($db['System'][self::ParamAdmins]);
    return $db['System'][self::ParamAdmins];
  }

  public function CommandAdd(string $Command, string $Module):bool{
    DebugTrace();
    $db = $this->Open();
    if(isset($db['System'][self::ParamCommands][$Command])):
      return false;
    else:
      $db['System'][self::ParamCommands][$Command] = $Module;
      $this->Save($db);
      return true;
    endif;
  }

  public function CommandDel(string|array $Command):void{
    DebugTrace();
    if(is_string($Command)):
      $Command = [$Command];
    endif;
    $db = $this->Open();
    foreach($Command as $cmd):
      unset($db['System'][self::ParamCommands][$cmd]);
    endforeach;
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
   * @param int $User User ID to associate the listener. Not allowed in checkout and InlineQuery listeners
   */
  public function ListenerAdd(
    StbDbListeners $Listener,
    string $Function,
    int $User = null
  ):void{
    DebugTrace();
    if($this->NoUserListener($Listener)):
      $User = null;
    endif;
    $db = $this->Open($User);
    $db['System'][self::ParamListeners][$Listener->name][] = $Function;
    $this->Save($db, $User);
  }

  public function ListenerDel(
    StbDbListeners $Listener,
    string $Function,
    int $User = null
  ):bool{
    DebugTrace();
    if($this->NoUserListener($Listener)):
      $User = null;
    endif;
    $db = $this->Open($User);
    $index = array_search($Function, $db['System'][self::ParamListeners][$Listener->name]);
    if($index === false):
      return false;
    endif;
    unset($db['System'][self::ParamListeners][$Listener->name][$index]);
    ArrayDefrag($db['System'][self::ParamListeners][$Listener->name]);
    $this->Save($db, $User);
    return true;
  }

  public function ListenerGet(
    StbDbListeners $Listener,
    int $User = null
  ):array{
    DebugTrace();
    if($this->NoUserListener($Listener)):
      $User = null;
    endif;
    $db = $this->Open($User);
    return $db['System'][self::ParamListeners][$Listener->name] ?? [];
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

  public function ModuleInstall(string $Module):void{
    DebugTrace();
    $db = $this->Open();
    $db['System'][self::ParamModules][$Module] = time();
    $this->Save($db);
  }

  /**
   * List all installed modules or get the module installation timestamp
   * @param string $Module
   * @return array|int|null
   */
  public function Modules(string $Module = null):array|int|null{
    DebugTrace();
    $db = $this->Open();
    $db['System'][self::ParamModules] ??= [];
    if($Module === null):
      return $db['System'][self::ParamModules];
    else:
      return $db['System'][self::ParamModules][$Module] ?? null;
    endif;
  }

  public function ModuleUninstall(string $Module):void{
    DebugTrace();
    $db = $this->Open();
    unset($db['System'][self::ParamModules][$Module]);
    unset($db[$Module]);
    $this->Save($db);
  }

  public function VariableGet(
    string $Name,
    int $User = null
  ):mixed{
    DebugTrace();
    $db = $this->Open($User);
    return $db['System'][self::ParamVariables][$Name] ?? null;
  }

  public function VariableSet(
    string $Name,
    mixed $Value = null,
    int $User = null
  ):void{
    DebugTrace();
    $db = $this->Open($User);
    $db['System'][self::ParamVariables][$Name] = $Value;
    $this->Save($db, $User);
  }
}

class StbDatabaseModule{
  private readonly string $Module;

  private function OpenAll(int $User = null):array{
    DebugTrace();
    if($User === null):
      $file = DirDb . '/system.json';
    else:
      $file = DirDb . '/' . $User . '.json';
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
      $file = DirDb . '/system.json';
    else:
      $file = DirDb . '/' . $User . '.json';
    endif;
    $db = $this->OpenAll($User);
    $db[$this->Module] = $Db;
    $db = json_encode($db);
    DirCreate(dirname($file));
    file_put_contents($file, $db);
  }

  public function __construct(string $Module){
    DebugTrace();
    if($Module === 'System'):
      $Module .= 1;
    endif;
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
<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.12.20.02

namespace ProtocolLive\SimpleTelegramBot\StbObjects;
use PDO;
use PDOException;
use ProtocolLive\PhpLiveDb\{
  AndOr,
  Operators,
  Parenthesis,
  PhpLiveDb,
  Types
};
use ProtocolLive\TelegramBotLibrary\TgObjects\TgUser;

final class StbDatabase{

  public function __construct(
    private PhpLiveDb $Db
  ){
    DebugTrace();
  }

  public function Admin(
    int $User
  ):StbDbAdminData|false{
    DebugTrace();
    $consult = $this->Db->Select('chats');
    $consult->WhereAdd('chat_id', $User, Types::Int);
    $result = $consult->Run();
    if($result === []):
      return false;
    endif;
    return new StbDbAdminData($result[0]);
  }

  public function AdminAdd(
    int $User,
    int $Perms
  ):bool{
    DebugTrace();
    $consult = $this->Db->Select('chats');
    $consult->WhereAdd('chat_id', $User, Types::Int);
    $result = $consult->Run();
    if($result !== []):
      return false;
    endif;
    $consult = $this->Db->Insert('chats');
    $consult->FieldAdd('chat_id', $User, Types::Int);
    $consult->FieldAdd('perms', $Perms, Types::Int);
    $consult->FieldAdd('created', time(), Types::Int);
    try{
      $consult->Run();
      return true;
    }catch(PDOException){
      return false;
    }
  }

  public function AdminDel(
    int $User
  ):bool{
    DebugTrace();
    if($User === Admin):
      return false;
    endif;
    $consult = $this->Db->Update('chatss');
    $consult->FieldAdd('perms', StbDbAdminPerm::None->value, Types::Int);
    $consult->WhereAdd('chat_id', $User, Types::Int);
    $consult->Run();
    return true;
  }

  public function AdminEdit(
    int $User,
    int $Perms
  ):bool{
    DebugTrace();
    if($User === Admin):
      return false;
    endif;
    $consult = $this->Db->Update('chatss');
    $consult->FieldAdd('perms', $Perms, Types::Int);
    $consult->WhereAdd('chat_id', $User, Types::Int);
    $consult->WhereAdd(
      'perms',
      StbDbAdminPerm::None->value,
      Types::Int,
      Operators::Bigger
    );
    if($consult->Run() === 1):
      return true;
    else:
      return false;
    endif;
  }

  /**
   * @return StbDbAdminData[]
   */
  public function Admins():array{
    DebugTrace();
    $consult = $this->Db->Select('chats');
    $consult->WhereAdd(
      'perms',
      StbDbAdminPerm::None->value,
      Types::Int,
      Operators::Bigger
    );
    $result = $consult->Run();
    foreach($result as &$admin):
      $admin = new StbDbAdminData($admin);
    endforeach;
    return $result;
  }

  public function CallBackHashRun(
    string $Hash
  ):bool{
    DebugTrace();
    $consult = $this->Db->Select('callbackshash');
    $consult->WhereAdd('hash', $Hash, Types::Str);
    $result = $consult->Run();
    if($result === []):
      return false;
    endif;
    $function = json_decode($result[0]['method'], true);
    call_user_func_array(array_shift($function), $function);
    return true;
  }

  /**
   * The callback data are limited to 64 bytes. This function hash the function to be called
   */
  public function CallBackHashSet(
    array $Data
  ):string|false{
    DebugTrace();
    $Data = json_encode($Data);
    $hash = sha1($Data);
    $consult = $this->Db->select('callbackshash');
    $consult->WhereAdd('hash', $hash, Types::Str);
    $result = $consult->Run();
    if($result === []):
      $consult = $this->Db->Insert('callbackshash');
      $consult->FieldAdd('hash', $hash, Types::Str);
    else:
      $consult = $this->Db->Update('callbackshash');
      $consult->WhereAdd('hash', $hash, Types::Str);
    endif;
    $consult->FieldAdd('method', $Data, Types::Str);
    $consult->Run(HtmlSafe: false);
    return $hash;
  }

  public function CommandAdd(string $Command, string $Module):bool{
    DebugTrace();
    $consult = $this->Db->Insert('commands');
    $consult->FieldAdd('command', $Command, Types::Str);
    $consult->FieldAdd('module', $Module, Types::Str);
    try{
      $consult->Run();
      return true;
    }catch(PDOException){
      return false;
    }
  }

  public function CommandDel(string|array $Command):void{
    DebugTrace();
    if(is_string($Command)):
      $Command = [$Command];
    endif;
    $consult = $this->Db->Delete('commands');
    $consult->WhereAdd(1, Parenthesis: Parenthesis::Open);
    foreach($Command as $id => $cmd):
      $consult->WhereAdd(
        'command',
        $cmd,
        Types::Str,
        AndOr: AndOr::Or,
        CustomPlaceholder: 'cmd' . $id
      );
    endforeach;
    $consult->WhereAdd(2, Parenthesis: Parenthesis::Close);
    $consult->Run();
  }

  /**
   * List all commands or check if a commands exists
   * @param string $Command
   * @return array Return all commands or the respective module
   */
  public function Commands(string $Command = null):array{
    DebugTrace();
    $consult = $this->Db->Select('commands');
    if($Command !== null):
      $consult->WhereAdd('command', $Command, Types::Str);
    endif;
    return $consult->Run();
  }

  public function GetCustom():PDO{
    return $this->Db->GetCustom();
  }

  /**
   * @param int $User User ID to associate the listener. Not allowed in checkout and InlineQuery listeners
   */
  public function ListenerAdd(
    StbDbListeners $Listener,
    string $Class,
    int $User = null
  ):bool{
    DebugTrace();
    if($this->NoUserListener($Listener)):
      $User = null;
    endif;
    $consult = $this->Db->InsertUpdate('listeners');
    $consult->FieldAdd(':listener', $Listener->name, Types::Str);
    $consult->FieldAdd(':module', $Class, Types::Str, Update: true);
    $consult->FieldAdd(':chat_id', $User, Types::Str, Update: true);
    try{
      $consult->Run();
      return true;
    }catch(PDOException){
      return false;
    }
  }

  public function ListenerDel(
    StbDbListeners $Listener,
    int $User = null
  ):void{
    DebugTrace();
    if($this->NoUserListener($Listener)):
      $User = null;
    endif;
    $consult = $this->Db->Delete('listeners');
    $consult->WhereAdd('listener', $Listener->name, Types::Str);
    $consult->WhereAdd('chat_id', $User, Types::Int);
    $consult->Run();
  }

  public function ListenerGet(
    StbDbListeners $Listener,
    int $User = null
  ):array{
    DebugTrace();
    if($this->NoUserListener($Listener)):
      $User = null;
    endif;
    $consult = $this->Db->Select('listeners');
    $consult->WhereAdd('listener', $Listener->name, Types::Str);
    $consult->WhereAdd('chat_id', $User, Types::Int);
    return $consult->Run();
  }

  public function ModuleInstall(string $Module):bool{
    DebugTrace();
    if($this->ModuleRestricted($Module)):
      return false;
    endif;
    $consult = $this->Db->Insert('modules');
    $consult->FieldAdd('module', $Module, Types::Str);
    $consult->FieldAdd('created', time(), Types::Int);
    try{
      $consult->Run();
      return true;
    }catch(PDOException){
      return false;
    }
  }

  public function ModuleRestricted(string $Module):bool{
    DebugTrace();
    if(strpos($Module, '\Stb') === false
    and strpos($Module, '\Tbl') === false
    and strpos($Module, '\Tg') === false):
      return false;
    endif;
    return true;
  }

  /**
   * List all installed modules or get the module installation timestamp
   * @param string $Module
   * @return array
   */
  public function Modules(string $Module = null):array{
    DebugTrace();
    $consult = $this->Db->Select('modules');
    if($Module !== null):
      $consult->WhereAdd('module', $Module, Types::Str);
    endif;
    $consult->Order('module');
    return $consult->Run();
  }

  /**
   * Removes listeners, callback hashes and module data before uninstall
   */
  public function ModuleUninstall(string $Module):void{
    DebugTrace();
    $consult = $this->Db->Delete('modules');
    $consult->WhereAdd('module', $Module, Types::Str);
    $consult->Run();
    $consult = $this->Db->Delete('callbackshash');
    $consult->WhereAdd(
      'data',
      '%' . $Module . '%',
      Types::Str,
      Operators::Like
    );
    $consult->Run();
  }

  private function NoUserListener(
    StbDbListeners $Listener
  ):bool{
    DebugTrace();
    if($Listener === StbDbListeners::InlineQuery
    or $Listener === StbDbListeners::ChatMy):
      return true;
    else:
      return false;
    endif;
  }

  public function UsageLog(
    int $Id,
    string $Event,
    string $Additional = null
  ):void{
    DebugTrace();
    $consult = $this->Db->Insert('sys_logs');
    $consult->FieldAdd('chat_id', $Id, Types::Int);
    $consult->FieldAdd('time', time(), Types::Int);
    $consult->FieldAdd('event', $Event, Types::Str);
    $consult->FieldAdd('additional', $Additional, Types::Str);
    $consult->Run();
  }

  public function UserEdit(
    TgUser $User
  ):bool{
    DebugTrace();
    $consult = $this->Db->InsertUpdate('chats');
    $consult->FieldAdd(':chat_id', $User->Id, Types::Int);
    $consult->FieldAdd(':name', $User->Name, Types::Str, Update: true);
    $consult->FieldAdd(':name2', $User->NameLast, Types::Str, Update: true);
    $consult->FieldAdd(':nick', $User->Nick, Types::Str, Update: true);
    $consult->FieldAdd(':lang', $User->Language, Types::Str, Update: true);
    try{
      $consult->Run();
      return true;
    }catch(PDOException){
      return false;
    }
  }

  public function UserGet(
    int $Id
  ):TgUser|null{
    DebugTrace();
    $consult = $this->Db->Select('chats');
    $consult->WhereAdd('chat_id', $Id, Types::Int);
    $result = $consult->Run();
    if($result === []):
      return null;
    endif;
    $return = [
      'id' => $result[0]['chat_id'],
      'first_name' => $result[0]['name'],
      'last_name' => $result[0]['name2'],
      'username' => $result[0]['nick'],
      'language_code' => $result[0]['lang']
    ];
    return new TgUser($return);
  }

  public function UserSeen(TgUser $User):void{
    DebugTrace();
    $user = $this->UserGet($User->Id);
    if($user === null):
      $consult = $this->Db->Insert('chats');
      $consult->FieldAdd('chat_id', $User->Id, Types::Int);
      $consult->FieldAdd('created', time(), Types::Int);
    else:
      $consult = $this->Db->Update('chats');
      $consult->WhereAdd('chat_id', $User->Id, Types::Int);
    endif;
    $consult->FieldAdd('name', $User->Name, Types::Str);
    $consult->FieldAdd('name2', $User->NameLast, Types::Str);
    $consult->FieldAdd('nick', $User->Nick, Types::Str);
    $consult->FieldAdd('lastseen', time(), Types::Int);
    $consult->FieldAdd('lang', $User->Language, Types::Str);
    $consult->Run();
  }

  public function VariableGet(
    string $Name,
    int $User = null
  ):string|null{
    DebugTrace();
    $consult = $this->Db->Select('variables');
    $consult->WhereAdd('name', $Name, Types::Str);
    $consult->WhereAdd('chat_id', $User, Types::Int);
    $result = $consult->Run();
    if($result === []):
      return null;
    else:
      return $result[0]['value'];
    endif;
  }

  public function VariableSet(
    string $Name,
    mixed $Value = null,
    int $User = null
  ):void{
    DebugTrace();
    if($Value === null):
      $consult = $this->Db->Delete('variables');
      $consult->WhereAdd('name', $Name, Types::Str);
      $consult->WhereAdd('chat_id', $User, Types::Int);
    else:
      $consult = $this->Db->Select('variables');
      $consult->WhereAdd('name', $Name, Types::Str);
      $consult->WhereAdd('chat_id', $User, Types::Int);
      $result = $consult->Run();
      if($result === []):
        $consult = $this->Db->Insert('variables');
        $consult->FieldAdd('name', $Name, Types::Str);
        $consult->FieldAdd('chat_id', $User, Types::Int);
      else:
        $consult = $this->Db->Update('variables');
        $consult->WhereAdd('name', $Name, Types::Str);
        $consult->WhereAdd('chat_id', $User, Types::Int);
      endif;
      $consult->FieldAdd('value', $Value, Types::Str);
    endif;
    $consult->Run();
  }
}
<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/FuncoesComuns
//2022.08.28.02

namespace ProtocolLive\SimpleTelegramBot\StbObjects;
use ProtocolLive\TelegramBotLibrary\TgObjects\TgUser;

class StbDatabase{
  private \PhpLiveDb $Db;
  public string|null $DbError = null;

  public function __construct(
    \PhpLiveDb $Db
  ){
    DebugTrace();
    $this->Db = $Db;
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

  public function ModuleRestricted(string $Module):bool{
    DebugTrace();
    if(strpos($Module, '\Stb') === false
    and substr($Module, '\Tbl') === false
    and substr($Module, '\Tg') === false):
      return false;
    endif;
    return true;
  }

  public function Admin(
    int $User
  ):StbDbAdminData|false{
    DebugTrace();
    $consult = $this->Db->Select('chats');
    $consult->WhereAdd('chat_id', $User, \PhpLiveDbTypes::Int);
    $result = $consult->Run();
    if($result === []):
      return false;
    endif;
    return new StbDbAdminData(
      $User,
      $result[0]['created'],
      StbDbAdminPerm::from($result[0]['perms']),
      $result[0]['name']
    );
  }

  public function AdminAdd(
    int $User,
    int $Perms
  ):bool{
    DebugTrace();
    $consult = $this->Db->Select('chatss');
    $consult->WhereAdd('chat_id', $User, \PhpLiveDbTypes::Int);
    $result = $consult->Run();
    if($result !== []):
      return false;
    endif;
    $consult = $this->Db->Insert('chatss');
    $consult->FieldAdd('chat_id', $User, \PhpLiveDbTypes::Int);
    $consult->FieldAdd('perms', $Perms, \PhpLiveDbTypes::Int);
    $consult->FieldAdd('created', time(), \PhpLiveDbTypes::Int);
    $consult->Run();
    $this->DbError = $consult->Error;
    if($this->DbError === null):
      return true;
    else:
      return false;
    endif;
  }

  public function AdminDel(
    int $User
  ):bool{
    DebugTrace();
    if($User === Admin):
      return false;
    endif;
    $consult = $this->Db->Update('chatss');
    $consult->FieldAdd('perms', StbDbAdminPerm::None->value, \PhpLiveDbTypes::Int);
    $consult->WhereAdd('chat_id', $User, \PhpLiveDbTypes::Int);
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
    $consult->FieldAdd('perms', $Perms, \PhpLiveDbTypes::Int);
    $consult->WhereAdd('chat_id', $User, \PhpLiveDbTypes::Int);
    $consult->WhereAdd(
      'perms',
      StbDbAdminPerm::None->value,
      \PhpLiveDbTypes::Int,
      \PhpLiveDbOperators::Bigger
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
      \PhpLiveDbTypes::Int,
      \PhpLiveDbOperators::Bigger
    );
    $result = $consult->Run();
    foreach($result as &$admin):
      $admin = new StbDbAdminData(
        $admin['chat_id'],
        $admin['created'],
        StbDbAdminPerm::from($admin['perms']),
        $result[0]['name']
      );
    endforeach;
    return $result;
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
    $consult->WhereAdd('hash', $hash, \PhpLiveDbTypes::Str);
    $result = $consult->Run();
    if($result === []):
      $consult = $this->Db->Insert('callbackshash');
      $consult->FieldAdd('hash', $hash, \PhpLiveDbTypes::Str);
    else:
      $consult = $this->Db->Update('callbackshash');
      $consult->WhereAdd('hash', $hash, \PhpLiveDbTypes::Str);
    endif;
    $consult->FieldAdd('method', $Data, \PhpLiveDbTypes::Str);
    $consult->Run(HtmlSafe: false);
    return $hash;
  }

  public function CallBackHashRun(
    string $Hash
  ):bool{
    DebugTrace();
    $consult = $this->Db->Select('callbackshash');
    $consult->WhereAdd('hash', $Hash, \PhpLiveDbTypes::Str);
    $result = $consult->Run();
    if($result === []):
      return false;
    endif;
    $function = json_decode($result[0]['method'], true);
    call_user_func_array(array_shift($function), $function);
    return true;
  }

  public function CommandAdd(string $Command, string $Module):bool{
    DebugTrace();
    $consult = $this->Db->Insert('commands');
    $consult->FieldAdd('command', $Command, \PhpLiveDbTypes::Str);
    $consult->FieldAdd('module', $Module, \PhpLiveDbTypes::Str);
    $consult->Run();
    $this->DbError = $consult->Error;
    if($this->DbError === null):
      return true;
    else:
      return false;
    endif;
  }

  public function CommandDel(string|array $Command):void{
    DebugTrace();
    if(is_string($Command)):
      $Command = [$Command];
    endif;
    $consult = $this->Db->Delete('commands');
    $consult->WhereAdd(1, Parenthesis: \PhpLiveDbParenthesis::Open);
    foreach($Command as $id => $cmd):
      $consult->WhereAdd(
        'command',
        $cmd,
        \PhpLiveDbTypes::Str,
        AndOr: \PhpLiveDbAndOr::Or,
        CustomPlaceholder: 'cmd' . $id
      );
    endforeach;
    $consult->WhereAdd(2, Parenthesis: \PhpLiveDbParenthesis::Close);
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
      $consult->WhereAdd('command', $Command, \PhpLiveDbTypes::Str);
    endif;
    return $consult->Run();
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
    $consult = $this->Db->Insert('listeners');
    $consult->FieldAdd('listener', $Listener->name, \PhpLiveDbTypes::Str);
    $consult->FieldAdd('module', $Class, \PhpLiveDbTypes::Str);
    $consult->FieldAdd('chat_id', $User, \PhpLiveDbTypes::Int);
    $consult->Run();
    $this->DbError = $consult->Error;
    if($this->DbError === null):
      return true;
    else:
      return false;
    endif;
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
    $consult->WhereAdd('listener', $Listener->name, \PhpLiveDbTypes::Str);
    $consult->WhereAdd('chat_id', $User, \PhpLiveDbTypes::Int);
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
    $consult->WhereAdd('listener', $Listener->name, \PhpLiveDbTypes::Str);
    $consult->WhereAdd('chat_id', $User, \PhpLiveDbTypes::Int);
    return $consult->Run();
  }

  public function ModuleInstall(string $Module):bool{
    DebugTrace();
    if($this->ModuleRestricted($Module)):
      return false;
    endif;
    $consult = $this->Db->Insert('modules');
    $consult->FieldAdd('module', $Module, \PhpLiveDbTypes::Str);
    $consult->FieldAdd('created', time(), \PhpLiveDbTypes::Int);
    $consult->Run();
    $this->DbError = $consult->Error;
    if($this->DbError === null):
      return true;
    else:
      return false;
    endif;
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
      $consult->WhereAdd('module', $Module, \PhpLiveDbTypes::Str);
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
    $consult->WhereAdd('module', $Module, \PhpLiveDbTypes::Str);
    $consult->Run();
    $consult = $this->Db->Delete('callbackshash');
    $consult->WhereAdd(
      'data',
      '%' . $Module . '%',
      \PhpLiveDbTypes::Str,
      \PhpLiveDbOperators::Like
    );
    $consult->Run();
  }

  public function UsageLog(
    int $Id,
    string $Event,
    string $Additional = null
  ):void{
    DebugTrace();
    $consult = $this->Db->Insert('sys_logs');
    $consult->FieldAdd('chat_id', $Id, \PhpLiveDbTypes::Int);
    $consult->FieldAdd('time', time(), \PhpLiveDbTypes::Int);
    $consult->FieldAdd('event', $Event, \PhpLiveDbTypes::Str);
    $consult->FieldAdd('additional', $Additional, \PhpLiveDbTypes::Str);
    $consult->Run();
  }

  public function UserEdit(
    TgUser $User
  ):bool{
    DebugTrace();
    $consult = $this->Db->Update('chats');
    $consult->WhereAdd('chat_id', $User->Id, \PhpLiveDbTypes::Int);
    $consult->FieldAdd('name', $User->Name, \PhpLiveDbTypes::Str);
    $consult->FieldAdd('name2', $User->NameLast, \PhpLiveDbTypes::Str);
    $consult->FieldAdd('nick', $User->Nick, \PhpLiveDbTypes::Str);
    $result = $consult->Run();
    $this->DbError = $consult->Error;
    return $result === 1;
  }

  public function UserGet(
    int $Id
  ):TgUser|null{
    DebugTrace();
    $consult = $this->Db->Select('chats');
    $consult->WhereAdd('chat_id', $Id, \PhpLiveDbTypes::Int);
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
      $consult->FieldAdd('chat_id', $User->Id, \PhpLiveDbTypes::Int);
      $consult->FieldAdd('created', time(), \PhpLiveDbTypes::Int);
    else:
      $consult = $this->Db->Update('chats');
      $consult->WhereAdd('chat_id', $User->Id, \PhpLiveDbTypes::Int);
    endif;
    $consult->FieldAdd('name', $User->Name, \PhpLiveDbTypes::Str);
    $consult->FieldAdd('name2', $User->NameLast, \PhpLiveDbTypes::Str);
    $consult->FieldAdd('nick', $User->Nick, \PhpLiveDbTypes::Str);
    $consult->FieldAdd('lastseen', time(), \PhpLiveDbTypes::Int);
    $consult->FieldAdd('lang', $User->Language, \PhpLiveDbTypes::Str);
    $consult->Run();
  }

  public function VariableGet(
    string $Name,
    int $User = null
  ):mixed{
    DebugTrace();
    $consult = $this->Db->Select('variables');
    $consult->WhereAdd('name', $Name, \PhpLiveDbTypes::Str);
    $consult->WhereAdd('chat_id', $User, \PhpLiveDbTypes::Int);
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
      $consult->WhereAdd('name', $Name, \PhpLiveDbTypes::Str);
      $consult->WhereAdd('chat_id', $User, \PhpLiveDbTypes::Int);
    else:
      $consult = $this->Db->Select('variables');
      $consult->WhereAdd('name', $Name, \PhpLiveDbTypes::Str);
      $consult->WhereAdd('chat_id', $User, \PhpLiveDbTypes::Int);
      $result = $consult->Run();
      if($result === []):
        $consult = $this->Db->Insert('variables');
        $consult->FieldAdd('name', $Name, \PhpLiveDbTypes::Str);
        $consult->FieldAdd('chat_id', $User, \PhpLiveDbTypes::Int);
      else:
        $consult = $this->Db->Update('variables');
        $consult->WhereAdd('name', $Name, \PhpLiveDbTypes::Str);
        $consult->WhereAdd('chat_id', $User, \PhpLiveDbTypes::Int);
      endif;
      $consult->FieldAdd('value', $Value, \PhpLiveDbTypes::Str);
    endif;
    $consult->Run();
  }
}
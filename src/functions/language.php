<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/FuncoesComuns
//2022.07.17.00

abstract class StbLanguageMaster{
  protected string $Default;
  protected array $Translate;

  public function Get(
    string $Text,
    string $Language = null,
    string $Group = null
  ):string|null{
    DebugTrace();
    if($Text === 'Commands'):
      return null;
    endif;
    if($Language === null):
      $lang = $this->Default;
    else:
      $lang = $Language;
    endif;
    if($Group === null):
      return $this->Translate[$lang][$Text];
    else:
      return $this->Translate[$lang][$Group][$Text];
    endif;
  }

  public function CommandsGet(
    string $Language
  ):array{
    DebugTrace();
    return $this->Translate[$Language]['Commands'];
  }

  public function LanguagesGet():array{
    DebugTrace();
    return array_keys($this->Translate);
  }
}

class StbLanguageSys extends StbLanguageMaster{
  public function __construct(string $Default){
    DebugTrace();
    $this->Default = $Default;
    foreach(glob(DirSystem . '/language/*', GLOB_ONLYDIR) as $dir):
      foreach(glob($dir . '/*.json') as $file):
        $temp = file_get_contents($file);
        $temp = json_decode($temp, true);
        $index = basename(dirname($file));
        $this->Translate[$index] = array_merge_recursive(
          $this->Translate[$index] ?? [],
          $temp
        );
      endforeach;
    endforeach;
  }
}

class StbLanguageModule extends StbLanguageMaster{
  public function __construct(string $Default){
    DebugTrace();
    $this->Default = $Default;
  }

  public function Load(string $Language, string $File):void{
    $temp = file_get_contents($File);
    $temp = json_decode($temp, true);
    $this->Translate[$Language] = array_merge_recursive(
      $this->Translate[$Language] ?? [],
      $temp
    );
  }
}
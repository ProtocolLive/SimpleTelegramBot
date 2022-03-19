<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/FuncoesComuns
//2022.03.19.00

class StbLanguage{
  private string $Default;
  private array $Translate;

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
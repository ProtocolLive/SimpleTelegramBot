<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/FuncoesComuns
//2022.03.15.00

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
        $this->Translate[$index] = array_merge(
          $this->Translate[$index] ?? [],
          $temp
        );
      endforeach;
    endforeach;
  }

  public function Get(string $Text, string $Language = null):string{
    DebugTrace();
    if($Language === null):
      $lang = $this->Default;
    else:
      $lang = $Language;
    endif;
    return $this->Translate[$lang][$Text];
  }
}
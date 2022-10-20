<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.08.27.00

namespace ProtocolLive\SimpleTelegramBot\StbObjects;

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
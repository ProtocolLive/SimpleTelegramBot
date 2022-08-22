<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/FuncoesComuns
//2022.08.22.00

namespace ProtocolLive\TelegramBotLibrary\StbObjects;

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
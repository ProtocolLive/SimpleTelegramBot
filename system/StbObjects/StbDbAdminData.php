<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.12.30.00

namespace ProtocolLive\SimpleTelegramBot\StbObjects;

final class StbDbAdminData{
  public readonly int $Id;
  public readonly int $Creation;
  public readonly int $Perms;
  public readonly string $Name;
  public readonly string|null $NameLast;
  public readonly string|null $Nick;
  public readonly string|null $Language;

  public function __construct(array $Data){
    $this->Id = $Data['chat_id'];
    $this->Creation = $Data['created'];
    $this->Perms = $Data['perms'];
    $this->Name = $Data['name'];
    $this->NameLast = $Data['name2'];
    $this->Nick = $Data['nick'];
    $this->Language = $Data['lang'];
  }
}
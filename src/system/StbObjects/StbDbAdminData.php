<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/FuncoesComuns
//2022.08.27.00

namespace ProtocolLive\SimpleTelegramBot\StbObjects;

class StbDbAdminData{
  public function __construct(
    public readonly int $Id,
    public readonly int $Creation,
    public readonly StbDbAdminPerm $Perms,
    public readonly string $Name,
  ){}
}
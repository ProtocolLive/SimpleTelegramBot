<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.09.16.00

namespace ProtocolLive\SimpleTelegramBot\StbObjects;

class StbDbAdminData{
  public function __construct(
    public readonly int $Id,
    public readonly int $Creation,
    public readonly StbDbAdminPerm $Perms,
    public readonly string $Name,
    public readonly string|null $NameLast,
    public readonly string|null $Language
  ){}
}
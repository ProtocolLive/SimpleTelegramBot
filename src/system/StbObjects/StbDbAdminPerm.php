<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/FuncoesComuns
//2022.08.22.00

namespace ProtocolLive\TelegramBotLibrary\StbObjects;

enum StbDbAdminPerm:int{
  case All = 15;
  case None = 0;
  case Admins = 1;
  case Modules = 2;
  case UserCmds = 4;
  case Stats = 8;
}
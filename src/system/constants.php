<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.04.25.00

define('DirSystem', dirname(__DIR__, 1));

enum StbEvents:int{
  case Text = 0;
  case Image = 1;
  case Document = 2;
  case Voice = 3;
}

abstract class StbDebug{
  const Trace = 16;
  const Bot = 32;
}
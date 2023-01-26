<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2023.01.26.00

define('DirSystem', dirname(__DIR__));
const DirDb = DirBot . '/db';
const DirModules = DirBot . '/modules';
const DirUserCmds = DirBot . '/UserCmds';

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
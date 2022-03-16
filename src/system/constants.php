<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.03.16.00

define('DirSystem', dirname(__DIR__, 1));

enum StbEvents:int{
  case Text = 0;
  case Image = 1;
  case Document = 2;
  case Voice = 3;
}

abstract class StbDebug{
  const Trace = 16;
}

abstract class DbParam{
  const Callbacks = 'Callbacks';
  const Commands = 'Commands';
  const ListenerText = 'ListenerText';
  const Modules = 'Modules';
}
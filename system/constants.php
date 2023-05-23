<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2023.05.23.00

define('DirSystem', dirname(__DIR__));
const DirModules = DirBot . '/modules';
const DirTextCmds = DirBot . '/TextCmds';

abstract class StbDebug{
  const Trace = 16;
  const Bot = 32;
}
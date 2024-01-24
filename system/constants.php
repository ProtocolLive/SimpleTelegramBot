<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2023.10.10.00

define('DirSystem', dirname(__DIR__));
const DirModules = DirBot . '/modules';
const DirTextCmds = DirBot . '/TextCmds';
const DirWebapps = DirSystem . '/WebApps';

abstract class StbDebug{
  const Trace = 16;
  const Bot = 32;
}
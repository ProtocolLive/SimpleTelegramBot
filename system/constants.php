<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot

define('DirSystem', dirname(__DIR__));
const DirModules = DirBot . '/modules';
const DirTextCmds = DirBot . '/TextCmds';
const DirWebapps = DirSystem . '/WebApps';

/**
 * @version 2024.02.14.00
 */
abstract class StbDebug{
  public const Trace = 16;
  public const Bot = 32;
}
<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.08.22.01

require(__DIR__ . '/constants.php');
require(DirSystem . '/system/functions/debug.php');
require(DirSystem . '/system/functions/basics.php');
require(DirSystem . '/system/PhpLiveDb/index.php');
require(DirSystem . '/system/TelegramBotLibrary/index.php');

require(DirSystem . '/system/StbObjects/StbDatabase.php');
require(DirSystem . '/system/StbObjects/StbDbAdminData.php');
require(DirSystem . '/system/StbObjects/StbDbAdminPerm.php');
require(DirSystem . '/system/StbObjects/StbDbListeners.php');
require(DirSystem . '/system/StbObjects/StbLanguageMaster.php');
require(DirSystem . '/system/StbObjects/StbLanguageModule.php');
require(DirSystem . '/system/StbObjects/StbLanguageSys.php');

require(DirSystem . '/functions/bot.php');
require(DirSystem . '/functions/language.php');
require(DirSystem . '/functions/module.php');

require(DirSystem . '/cmds/admin.php');
require(DirSystem . '/cmds/modules.php');
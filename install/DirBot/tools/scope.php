<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.12.23.00

define('DirBot', dirname(__DIR__, 1));
require(dirname(__DIR__, 2) . '/system/system.php');

/**
 * @var TelegramBotLibrary $Bot
 */

$lang = substr(DefaultLanguage, 0, 2);?>

<p>
  Default<br>
  <?php var_dump($Bot->MyCmdGet(TgCmdScope::Default));?>
</p>
<p>
  Default + <?php echo $lang;?><br>
  <?php var_dump($Bot->MyCmdGet(TgCmdScope::Default, $lang));?>
</p>
<p>
  Users<br>
  <?php var_dump($Bot->MyCmdGet(TgCmdScope::Users));?>
</p>
<p>
  Users + <?php echo $lang;?><br>
  <?php var_dump($Bot->MyCmdClear(TgCmdScope::Users, $lang));?>
</p>
<p>
  Groups<br>
  <?php var_dump($Bot->MyCmdGet(TgCmdScope::Groups));?>
</p>
<p>
  Groups + <?php echo $lang;?><br>
  <?php var_dump($Bot->MyCmdGet(TgCmdScope::Groups, $lang));?>
</p>
<p>
  Groups Admins<br>
  <?php var_dump($Bot->MyCmdGet(TgCmdScope::GroupsAdmins));?>
</p>
<p>
  Groups Admins + <?php echo $lang;?><br>
  <?php var_dump($Bot->MyCmdGet(TgCmdScope::GroupsAdmins, $lang));?>
</p>
<p>
  Main admin<br>
  <?php var_dump($Bot->MyCmdGet(TgCmdScope::Chat, null, Admin));?>
</p>
<p>
  Main admin + <?php echo $lang;?><br>
  <?php var_dump($Bot->MyCmdClear(TgCmdScope::Chat, $lang, Admin));?>
</p>
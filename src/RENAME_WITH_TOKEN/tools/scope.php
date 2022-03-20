<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.03.19.00

require(dirname(__DIR__, 2) . '/system/system.php');

/**
 * @var TelegramBotLibrary $Bot
 */

$lang = substr(DefaultLanguage, 0, 2);?>

<p>
  Default<br>
  <?php var_dump($Bot->CmdGet(TgCmdScope::Default));?>
</p>
<p>
  Default + language<br>
  <?php var_dump($Bot->CmdGet(TgCmdScope::Default, $lang));?>
</p>
<p>
  Users<br>
  <?php var_dump($Bot->CmdGet(TgCmdScope::Users));?>
</p>
<p>
  Users + language<br>
  <?php var_dump($Bot->CmdGet(TgCmdScope::Users, $lang));?>
</p>
<p>
  Groups<br>
  <?php var_dump($Bot->CmdGet(TgCmdScope::Groups));?>
</p>
<p>
  Groups + language<br>
  <?php var_dump($Bot->CmdGet(TgCmdScope::Groups, $lang));?>
</p>
<p>
  Groups Admins<br>
  <?php var_dump($Bot->CmdGet(TgCmdScope::GroupsAdmins));?>
</p>
<p>
  Groups Admins + language<br>
  <?php var_dump($Bot->CmdGet(TgCmdScope::GroupsAdmins, $lang));?>
</p>
<p>
  Main admin<br>
  <?php var_dump($Bot->CmdGet(TgCmdScope::Chat, null, Admin));?>
</p>
<p>
  Main admin + language<br>
  <?php var_dump($Bot->CmdGet(TgCmdScope::Chat, $lang, Admin));?>
</p>
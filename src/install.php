<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.04.26.01

require(__DIR__ . '/system/php.php');

$_GET['a'] ??= '';
if(basename(__FILE__) !== 'install.php'):
  echo 'Protocol SimpleTelegramBot already installed!';
elseif(function_exists('Action_' . $_GET['a'])):
  call_user_func('Action_' . $_GET['a']);
endif;

function Action_():void{?>
  <form method="post" action="install.php?a=ok">
    <table>
      <tr>
        <td>Timezone:</td>
        <td>
          <select name="timezone"><?php
            foreach(DateTimeZone::listIdentifiers(DateTimeZone::ALL) as $name):?>
              <option value="<?php echo $name;?>"><?php echo $name;?></option><?php
            endforeach;?>
          </select>
        </td>
      </tr>
      <tr>
        <td>Token:</td>
        <td>
          <input type="text" name="token">
        </td>
      </tr>
      <tr>
        <td>Test server:</td>
        <td>
          <select name="testserver">
            <option value="false">No</option>
            <option value="true">Yes</option>
          </select>
        </td>
      </tr>
      <tr>
        <td>Default language:</td>
        <td>
          <select name="language">
            <option value="en">English</option>
            <option value="pt-br">Portuguese Brazil</option>
          </select>
        </td>
      </tr>
      <tr>
        <td>Admin ID:</td>
        <td>
          <input type="text" name="admin">
        </td>
      </tr>
    </table>
    <p>
      <input type="submit" value="Install">
    </p>
  </form><?php
}

function Action_ok():void{
  $config = file_get_contents(__DIR__ . '/config.php');
  $config = str_replace('##DATE##', date('Y-m-d H:i:s'), $config);
  $config = str_replace('##TIMEZONE##', $_POST['timezone'], $config);
  $config = str_replace('##TOKEN##', $_POST['token'], $config);
  $config = str_replace('##TESTSERVER##', $_POST['testserver'], $config);
  $config = str_replace('##LANGUAGE##', $_POST['language'], $config);
  $config = str_replace('##ADMIN##', $_POST['admin'], $config);
  file_put_contents(__DIR__ . '/config.php', $config);
  $token = explode(':', $_POST['token']);
  rename(__DIR__ . '/RENAME_WITH_TOKEN', __DIR__ . '/' . $token[1]);
  rename(__DIR__ . '/install.php', __DIR__ . '/_install.php');
  echo 'Install complete!';
}
<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.04.29.00

require(__DIR__ . '/system/php.php');
set_error_handler('error');
set_exception_handler('error');

$_GET['a'] ??= '';
if(basename(__FILE__) !== 'index.php'):
  echo 'Protocol SimpleTelegramBot already installed!';
elseif(function_exists('Action_' . $_GET['a'])):
  call_user_func('Action_' . $_GET['a']);
endif;

function Action_():void{?>
  <h1>SimpleTelegramBot Install</h1>
  <form method="post" action="index.php?a=ok">
    <table>
      <tr>
        <td>Token:</td>
        <td>
          <input type="text" name="token">
        </td>
      </tr>
      <tr>
        <td>Admin ID:</td>
        <td>
          <input type="text" name="admin">
        </td>
      </tr>
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
        <td style="vertical-align:top">Default language:</td>
        <td>
          <select name="language">
            <option value="en">English</option>
            <option value="pt-br">Portuguese Brazil</option>
          </select><br>
          Another language? Add later in config.php
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
    </table>
    <p>
      <input type="submit" value="Install">
    </p>
  </form><?php
}

function Action_ok():void{
  echo '<h1>SimpleTelegramBot Install</h1>';
  $token = explode(':', $_POST['token']);
  $token = $token[1];

  $config = file_get_contents(__DIR__ . '/config.php');
  $config = str_replace('##DATE##', date('Y-m-d H:i:s'), $config);
  $config = str_replace('##TIMEZONE##', $_POST['timezone'], $config);
  $config = str_replace('##TOKEN##', $_POST['token'], $config);
  $config = str_replace('##TESTSERVER##', $_POST['testserver'], $config);
  $config = str_replace('##LANGUAGE##', $_POST['language'], $config);
  $config = str_replace('##ADMIN##', $_POST['admin'], $config);
  $config = str_replace('##DIRTOKEN##', __DIR__ . '/' . $token, $config);
  file_put_contents(__DIR__ . '/config.php', $config);

  rename(__DIR__ . '/RENAME_WITH_TOKEN', __DIR__ . '/' . $token);
  file_put_contents(__DIR__ . '/' . $token . '/system.php', '{}');
  rename(__DIR__ . '/index.php', __DIR__ . '/install.php');

  echo '✅ Install complete!';
  $url = dirname($_SERVER['SCRIPT_URI']);
  $url .= '/' . $token . '/index.php?a=WebhookSet';
  echo '<p><a href="' . $url . '">Click here to set the webhook</a></p>';
}

function error():never{
  echo '<p>⚠️ Install error!</p>';
  $args = func_get_args();
  echo '<pre>' . $args[1] . '</pre>';
  die();
}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SimpleTelegramBot Install</title>
</head>
<body>
  <h1>SimpleTelegramBot Install</h1>
  <form method="post" action="index.php?step=2">
    <table>
      <tr>
        <td>Name:</td>
        <td>
          <input type="text" name="name">
        </td>
      </tr>
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
          <span style="font-size:11">Another language? Add later in bot config</span>
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
    </table><br>
    <table>
      <tr>
        <td>Database:</td>
        <td>
          <select name="dbtype">
            <option value="sqlite">SQLite</option>
            <option value="mysql">MySQL</option>
          </select>
        </td>
      </tr>
      <tr>
        <td>Host:</td>
        <td><input type="text" name="host"></td>
      </tr>
      <tr>
        <td>User:</td>
        <td><input type="text" name="user"></td>
      </tr>
      <tr>
        <td>Password:</td>
        <td><input type="text" name="pwd"></td>
      </tr>
      <tr>
        <td>Database name:</td>
        <td><input type="text" name="db"></td>
      </tr>
    </table>
    <p>
      <input type="submit" value="Install">
    </p>
  </form>
</body>
</html>
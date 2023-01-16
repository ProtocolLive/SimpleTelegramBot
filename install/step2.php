<?php
//2023.01.16.00

use ProtocolLive\PhpLiveDb\{
  Drivers,
  PhpLiveDb
};
use ProtocolLive\SimpleTelegramBot\StbObjects\StbDbAdminPerm;?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SimpleTelegramBot Install</title>
</head>
<body>
  <h1>SimpleTelegramBot Install</h1><?php

  $DirSystem = dirname(__DIR__, 1);
  $DirBot = 'Bot-' . $_POST['name'] . '-' . md5($_POST['token']);

  mkdir($DirSystem . '/DirBot', 0755, true);
  CopyRecursive(__DIR__ . '/DirBot', $DirSystem . '/DirBot');

  $config = file_get_contents($DirSystem . '/DirBot/config.php');
  $config = str_replace('##DATE##', date('Y-m-d H:i:s'), $config);
  $config = str_replace('##TIMEZONE##', $_POST['timezone'], $config);
  $config = str_replace('##TOKEN##', $_POST['token'], $config);
  $config = str_replace('##TESTSERVER##', $_POST['testserver'], $config);
  $config = str_replace('##LANGUAGE##', $_POST['language'], $config);
  $config = str_replace('##ADMIN##', $_POST['admin'], $config);
  $config = str_replace('##TOKENWEBHOOK##', "'" . hash('sha256', uniqid()) . "'", $config);

  if($_POST['dbtype'] === 'mysql'):
    $config = str_replace('##DBTYPE##', 'Drivers::MySql', $config);
  else:
    $config = str_replace('##DBTYPE##', 'Drivers::SqLite', $config);
  endif;
  $config = str_replace('##DBHOST##', $_POST['host'], $config);
  $config = str_replace('##DBUSER##', $_POST['user'], $config);
  $config = str_replace('##DBPWD##', $_POST['pwd'], $config);
  $config = str_replace('##DBNAME##', $_POST['db'], $config);
  file_put_contents($DirSystem . '/DirBot/config.php', $config);

  rename($DirSystem . '/DirBot', $DirSystem . '/' . $DirBot);

  if($_POST['dbtype'] === 'mysql'):
    $PlDb = new PhpLiveDb(
      $_POST['host'],
      $_POST['user'],
      $_POST['pwd'],
      $_POST['db']
    );
    $sqls = file_get_contents(__DIR__ . '/sql/mysql/install.sql');
  else:
    $PlDb = new PhpLiveDb(
      "$DirSystem/$DirBot/db.db",
      Driver: Drivers::SqLite
    );
    $sqls = file_get_contents(__DIR__ . '/sql/sqlite/install.sql');
  endif;
  $consult = $PlDb->GetCustom();
  $sqls = explode(';', $sqls);
  array_pop($sqls);
  foreach($sqls as $sql):
    $consult->exec($sql);
  endforeach;
  $stm = $consult->prepare('
    insert into chats(chat_id,created,perms)
    values(:admin,' . time() . ',' . StbDbAdminPerm::All->value . ')
  ');
  $stm->bindValue(':admin', $_POST['admin'], PDO::PARAM_INT);
  $stm->execute();

  rename(__DIR__, $DirSystem . '/install_' . uniqid());
  rename($DirSystem . '/index.php', $DirSystem . '/index_' . uniqid() . '.php');

  echo '✅ Install complete!';
  $url = dirname($_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME']);
  $url .= '/' . $DirBot . '/index.php?a=WebhookSet';
  echo '<p><a href="https://' . $url . '">Click here to set the webhook</a></p>';?>
</body>
</html>
<?php
//2022.08.28.00
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

  $token = explode(':', $_POST['token']);
  $token = $token[1];
  $DirSystem = dirname(__DIR__, 1);
  $DirToken = 'Bot-' . $_POST['name'] . '-' . $token;

  mkdir($DirSystem . '/DirToken', 0755, true);
  CopyRecursive(__DIR__ . '/DirToken', $DirSystem . '/DirToken');

  $config = file_get_contents($DirSystem . '/DirToken/config.php');
  $config = str_replace('##DATE##', date('Y-m-d H:i:s'), $config);
  $config = str_replace('##TIMEZONE##', $_POST['timezone'], $config);
  $config = str_replace('##TOKEN##', $_POST['token'], $config);
  $config = str_replace('##TESTSERVER##', $_POST['testserver'], $config);
  $config = str_replace('##LANGUAGE##', $_POST['language'], $config);
  $config = str_replace('##ADMIN##', $_POST['admin'], $config);
  $config = str_replace('##TOKENWEBHOOK##', "'" . hash('sha256', uniqid()) . "'", $config);
  file_put_contents($DirSystem . '/DirToken/config.php', $config);

  rename($DirSystem . '/DirToken', $DirSystem . '/Bot-' . $_POST['name'] . '-' . $token);

  $PlDb = new PhpLiveDb(
    "$DirSystem/$DirToken/db.db",
    Driver: PhpLiveDbDrivers::SqLite
  );
  $consult = $PlDb->GetCustom();
  $sqls = file_get_contents(__DIR__ . '/db.sql');
  $sqls = explode(';', $sqls);
  array_pop($sqls);
  $consult->beginTransaction();
  foreach($sqls as $sql):
    $stm = $consult->prepare($sql);
    $stm->execute();
  endforeach;
  $stm = $consult->prepare('
    insert into chats(chat_id,created,perms)
    values(:admin,' . time() . ',' . StbDbAdminPerm::All->value . ')
  ');
  $stm->bindValue(':admin', $_POST['admin'], PDO::PARAM_INT);
  $stm->execute();
  $consult->commit();

  echo '✅ Install complete!';
  $url = dirname($_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME']);
  $url .= '/' . $DirToken . '/index.php?a=WebhookSet';
  echo '<p><a href="https://' . $url . '">Click here to set the webhook</a></p>';

  rename(__DIR__, $DirSystem . '/install_' . time());
  rename($DirSystem . '/index.php', $DirSystem . '/index_' . time() . '.php');?>
</body>
</html>
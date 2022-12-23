<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.12.23.00

const DirBot = __DIR__;
require(dirname(__DIR__, 1) . '/system/system.php');

$consult = $PlDb->Select('chats');
$consult->Fields('count(*) as count');
$chats = $consult->Run();?>

<p>Lifetime users interacted: <?=$chats[0]['count'];?></p>

<p>
  <b>Commands</b><br><?php
  $consult = $PlDb->Select('sys_logs');
  $consult->Fields('event,count(event) as count');
  $consult->Group('event');
  $consult->Order('count desc');
  $consult->Run(Fetch: true);
  while(($event = $consult->Fetch()) !== false):
    echo $event['count'] . ' - ' . $event['event'] . '<br>';
  endwhile;?>
</p>

<p>
  <b>Logs</b><br><?php
  $consult = $PlDb->Select('sys_logs');
  $consult->JoinAdd('chats', 'chat_id');
  $consult->Order('time desc');
  $consult->Run(Fetch: true);
  while(($log = $consult->Fetch()) !== false):
    echo date('Y/m/d H:i:s', $log['time']) . ' - ';
    echo $log['event'] . ' ';
    if($log['additional'] !== null):
      echo $log['additional'];
    endif;
    echo '<br>';

    echo $log['chat_id'] . ', ';
    if($log['nick'] !== null):
      echo '@' . $log['nick'] . ', ';
    endif;
    echo $log['name'] . ' ';
    if($log['name2'] !== null):
      echo $log['name2'] . ' ';
    endif;
    echo '<hr>';
  endwhile;?>
</p>
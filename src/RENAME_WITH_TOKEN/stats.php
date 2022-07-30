<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2022.07.30.00

const DirToken = __DIR__;
require(dirname(__DIR__, 1) . '/system/system.php');

$consult = $PlDb->Select('chats');
$chats = $consult->Run();

$consult = $PlDb->Select('sys_logs');
$consult->Fields('event,count(event) as count');
$consult->Group('event');
$consult->Order('count desc');
$events = $consult->Run();

$consult = $PlDb->Select('sys_logs');
$consult->JoinAdd('chats', 'chat_id');
$consult->Order('time desc');
$logs = $consult->Run();

?>
<p>Lifetime users interacted: <?= count($chats);?></p>

<p>
  <b>Commands</b><br><?php
  foreach($events as $event):
    echo $event['count'] . ' - ' . $event['event'] . '<br>';
  endforeach;?>
</p>

<p>
  <b>Logs</b><br><?php
  foreach($logs as $log):
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
  endforeach;?>
</p>
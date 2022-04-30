<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2021.09.12.00

$ByUser = [];
$ByCom = [];

$file = fopen(__DIR__ . '/logs/usage.log', 'r');
while(feof($file) === false):
  $line = trim(fgets($file));
  if(strlen($line) > 0):
    $temp = explode("\t", $line);
    $temp2 = $temp[1];
    if(isset($temp[3])):
      $temp2 .= ' - ' . $temp[3];
    endif;
    $ByDay[explode(' ', $temp[0])[0]][$temp[2]][] = $temp2;
    $ByUser[$temp[1]][$temp[2]][] = $temp[0];
    $ByCom[$temp[2]][$temp[1]][] = $temp[0];
  endif;
endwhile;
$ByUser = array_reverse($ByUser);
ksort($ByCom);?>

<table style="width:100%">
  <tr>
    <th style="text-align:left;">By day</th>
    <th style="text-align:left;">By user</th>
    <th style="text-align:left;">By command</th>
  </tr>
  <tr>
    <td style="vertical-align:top;">
      Total: <?php print count($ByUser);?><br><?php
      $ByDay = array_reverse($ByDay);
      foreach($ByDay as $day => $commands):
        print $day . '<br>';
        foreach($commands as $command => $users):
          print '<span style="margin-left:15px">' . $command . '</span><br>';
          foreach($users as $user):
            print '<span style="margin-left:30px">' . $user . '</span><br>';
          endforeach;
        endforeach;
      endforeach;?>
    <td style="vertical-align:top;">
      Total: <?php print count($ByUser);?><br><?php
      foreach($ByUser as $name => $user):
        print $name . '<br>';
        foreach($user as $command => $times):
          print '<span style="margin-left:15px">' . $command . '</span><br>';
          foreach($times as $time):
            print '<span style="margin-left:30px">' . $time . '</span><br>';
          endforeach;
        endforeach;
      endforeach;?>
    </td>
    <td style="vertical-align:top;">
      Total: <?php print count($ByCom);?><br><?php
      foreach($ByCom as $command => $users):
        print $command . '<br>';
        foreach($users as $user => $times):
          print '<span style="margin-left:15px">' . $user . '</span><br>';
          foreach($times as $time):
            print '<span style="margin-left:30px">' . $time . '</span><br>';
          endforeach;
        endforeach;
      endforeach;?>
    </td>
  </tr>
</table>
<?php
//Protocol Corporation Ltda.
//https://github.com/ProtocolLive/SimpleTelegramBot
//2023.01.05.00

namespace ProtocolLive\SimpleTelegramBot\StbObjects;
use Exception;

final class StbCronTools{
  private array $Jobs = [];
  public string|null $Email = null;
  public string|null $Shell = null;

  /**
   * @throws Exception
   */
  public function __construct(){
    $temp = shell_exec('crontab -l');
    if($temp === null):
      throw new Exception('Impossible to work with cronjobs');
    endif;
    $temp = explode("\n", $temp);
    foreach($temp as $job):
      if(substr($job, 0, 6) === 'MAILTO'):
        $this->Email = substr($job, 8, -1);
      elseif(substr($job, 0, 5) === 'SHELL'):
        $this->Shell = substr($job, 7, -1);
      elseif($job === ''):
        continue;
      else:
        $temp = explode(' ', $job, 6);
        $this->Jobs[] = [
          'Minute' => $temp[0],
          'Hour' => $temp[1],
          'Day' => $temp[2],
          'Month' => $temp[3],
          'Week' => $temp[4],
          'Cmd' => $temp[5]
        ];
      endif;
    endforeach;
  }

  public function Add(
    string $Cmd,
    int $Minute = null,
    int $Hour = null,
    int $Day = null,
    int $Month = null,
    int $Week = null
  ):void{
    $this->Jobs[] = [
      'Minute' => $Minute ?? '*',
      'Hour' => $Hour ?? '*',
      'Day' => $Day ?? '*',
      'Month' => $Month ?? '*',
      'Week' => $Week ?? '*',
      'Cmd' => $Cmd
    ];
  }

  public function Del(
    int $Index
  ):void{
    unset($this->Jobs[$Index]);
  }

  public function Get(
    int $Index = null
  ):array{
    if($Index === null):
      return $this->Jobs;
    else:
      return $this->Jobs[$Index];
    endif;
  }

  public function Save():void{
    $temp = '';
    if($this->Email !== null):
      $temp .= 'MAILTO="' . $this->Email . '"' . "\n";
    endif;
    if($this->Shell !== null):
      $temp .= 'SHELL="' . $this->Shell . '"' . "\n";
    endif;
    foreach($this->Jobs as $job):
      $temp .= implode(' ', $job) . "\n";
    endforeach;
    file_put_contents(__DIR__ . '/crontab.txt', $temp);
    shell_exec('crontab ' . __DIR__ . '/crontab.txt');
    unlink(__DIR__ . '\crontab.txt');
  }
}
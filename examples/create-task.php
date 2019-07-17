<?php

require dirname(__FILE__) . '/../vendor/autoload.php';

use SignalWire\Relay\Tasking\Task;

$project = isset($_ENV['PROJECT']) ? $_ENV['PROJECT'] : '';
$token = isset($_ENV['TOKEN']) ? $_ENV['TOKEN'] : '';

$task = new Task($project, $token);
$success = $task->deliver('office', [
  'key' => 'value',
  'data' => 'random stuff'
]);

<?php

/// This file shows how to send a Task to a Consumer on the "office" context.
///
/// See the related handle-tasks.php file to see how to handle your Task
/// within the Consumer!

require dirname(__FILE__) . '/../../vendor/autoload.php';

use SignalWire\Relay\Task;

$project = isset($_ENV['PROJECT']) ? $_ENV['PROJECT'] : '';
$token = isset($_ENV['TOKEN']) ? $_ENV['TOKEN'] : '';

$task = new Task($project, $token);
$context = 'office';
$data = [
  'key' => 'value',
  'data' => 'random stuff'
];
$success = $task->deliver($context, $data);

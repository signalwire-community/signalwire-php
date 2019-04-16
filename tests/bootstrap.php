<?php
declare(strict_types=1);
require dirname(__FILE__) . '/../vendor/autoload.php';
\VCR\VCR::configure()
  ->setMode('once')
  ->enableRequestMatchers(array('method', 'url', 'host'));
\VCR\VCR::turnOn();


function mockConnectionSend(Array $responses) {
  $promises = array();
  foreach($responses as $r) {
    $promises[] = new \React\Promise\Promise(function (callable $resolve, callable $reject) use ($r) {
      isset($r->error) ? $reject($r->error) : $resolve($r->result);
    });
  }

  $mock = Mockery::mock('overload:\SignalWire\Relay\Connection');
  $mock->shouldReceive('send')->andReturn(...$promises);

  return $mock;
}

function getLoop() {
  return React\EventLoop\Factory::create();
}

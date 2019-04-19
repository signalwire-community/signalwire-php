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
    $promises[] = \React\Promise\resolve($r);
  }

  $mock = Mockery::mock('overload:\SignalWire\Relay\Connection');
  $mock->shouldReceive('send')->andReturn(...$promises);

  return $mock;
}

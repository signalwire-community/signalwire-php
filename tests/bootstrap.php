<?php
declare(strict_types=1);
require dirname(__FILE__) . '/../vendor/autoload.php';
\VCR\VCR::configure()
  ->setMode('once')
  ->enableRequestMatchers(array('method', 'url', 'host'));
\VCR\VCR::turnOn();


function mockConnectionSend($response) {
  $promise = new \React\Promise\Promise(function (callable $resolve, callable $reject) use ($response) {
    isset($response->error) ? $reject($response->error) : $resolve($response->result);
  });

  $mock = Mockery::mock('overload:\SignalWire\Relay\Connection');
  $mock->shouldReceive('send')
    ->once()
    ->andReturn($promise);

  return $mock;
}

function getLoop() {
  return React\EventLoop\Factory::create();
}

<?php
declare(strict_types=1);
require dirname(__FILE__) . '/../vendor/autoload.php';
\VCR\VCR::configure()
  ->setMode('once')
  ->enableRequestMatchers(array('method', 'url', 'host'));
\VCR\VCR::turnOn();


function mockUuidV4() {
  $mock = Mockery::mock('alias:SignalWire\Util\UUID');
  $mock->shouldReceive('v4')->andReturn('mocked-uuid');
}

mockUuidV4();

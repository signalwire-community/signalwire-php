<?php
declare(strict_types=1);
require dirname(__FILE__) . '/../vendor/autoload.php';
\VCR\VCR::configure()
  ->setMode('once')
  ->enableRequestMatchers(array('method', 'url', 'host'));
\VCR\VCR::turnOn();

\SignalWire\Log::getLogger()->pushHandler(new \Monolog\Handler\NullHandler());

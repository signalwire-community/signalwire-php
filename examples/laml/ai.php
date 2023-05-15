<?php
require dirname(__FILE__) . '/../../vendor/autoload.php';

$response = new SignalWire\LaML\VoiceResponse();
$conn = $response->connect();

$ai = $conn->ai();
$ai->setEngine('gcloud');
$p1 = $ai->prompt('prompt1');
$p1->setTemperature(0.2);
$ai->postPrompt('prompt2');

$swaig = $ai->swaig();
$swaig->defaults([ 'webHookURL' => "https://user:pass@server.com/commands.cgi"]);

$fn = $swaig->function();
$fn->setName('fn1');
$fn->setArgument('no argument');
$fn->setPurpose('to do something');

$fn = $swaig->function();
$fn->setName('fn2');
$fn->setArgument('no argument');
$fn->setPurpose('to do something');
$fn->addMetaData("AAA", "111");
$fn->addMetaData("BBB", "222");

print $response;
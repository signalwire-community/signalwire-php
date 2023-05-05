<?php
require dirname(__FILE__) . '/../../vendor/autoload.php';

$response = new SignalWire\LaML();
$response->say("Welcome to SignalWire!");
$response->play("https://cdn.signalwire.com/default-music/welcome.mp3", array("loop" => 5));

$response = new SignalWire\LaML\VoiceResponse();
    $dial = $response->dial();
    $ai = $dial->ai();
    $p1 = $ai->prompt('sciao');
    $p1->setTemperature(0.2);
    $ai->postPrompt('pp');

echo $response;

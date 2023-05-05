<?php
require dirname(__FILE__) . '/../../vendor/autoload.php';

$response = new SignalWire\LaML();
$response->say("Welcome to SignalWire!");
$response->play("https://cdn.signalwire.com/default-music/welcome.mp3", array("loop" => 5));

echo $response;

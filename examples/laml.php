<?php
require dirname(__FILE__) . '/../vendor/autoload.php';

$response = new SignalWire\LaML();
$response->say("Welcome to SignalWire!");
$response->play("https://ccrma.stanford.edu/~jos/mp3/gtr-nylon22.mp3", array("loop" => 5));

echo $response;
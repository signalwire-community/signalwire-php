<?php
require dirname(__FILE__) . '/../../vendor/autoload.php';

$response = new SignalWire\LaML\VoiceResponse();
$response->say('Hello');
$response->play('https://cdn.signalwire.com/default-music/welcome.mp3', ['loop' => 5]);
print $response;

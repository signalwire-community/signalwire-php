<?php
  require dirname(__FILE__) . '/../../vendor/autoload.php';
  use SignalWire\Rest\Client;

  $client = new Client('YourProjectID', 'YourAuthToken', array("signalwireSpaceUrl" => "example.signalwire.com"));

  $message = $client->messages
                    ->create("+1+++", // to
                             array("from" => "+1+++", "body" => "Hello World!")
                    );

  print($message->sid);
?>
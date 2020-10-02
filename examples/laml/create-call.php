<?php
  require dirname(__FILE__) . '/../../vendor/autoload.php';
  use SignalWire\Rest\Client;

  $client = new Client('YourProjectID', 'YourAuthToken', array("signalwireSpaceUrl" => "example.signalwire.com"));

  $call = $client->calls
                 ->create("+1+++", // to
                          "+1+++", // from
                          array("url" => "http://your-application.com/docs/voice.xml")
                 );

  print($call->sid);
?>

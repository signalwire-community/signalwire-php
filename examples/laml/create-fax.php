<?php
  require dirname(__FILE__) . '/../../vendor/autoload.php';
  use SignalWire\Rest\Client;

  $client = new Client('YourProjectID', 'YourAuthToken', array("signalwireSpaceUrl" => "example.signalwire.com"));

  $fax = $client->fax->v1->faxes
                         ->create("+1+++", // to
                                  "https://example.com/fax.pdf", // mediaUrl
                                  array("from" => "+1+++")
                         );

  print($fax->sid);
?>
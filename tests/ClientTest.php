<?php

use PHPUnit\Framework\TestCase;
use SignalWire\Rest\Client;

class ClientTest extends TestCase
{
  protected $sid = "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX";
  protected $token = "PTXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

  public function testRestEndpointWithEnv(): void {
    $domain = 'test.signalwire.com';
    $_ENV['SIGNALWIRE_API_HOSTNAME'] = $domain;

    $client = new Client($this->sid, $this->token);
    $this->assertEquals($client->api->baseUrl, "https://" . $domain);
  }

  public function testRestEndpointWithPutEnv(): void {
    $domain = 'test.signalwire.com';
    putenv("SIGNALWIRE_API_HOSTNAME=$domain");


    $client = new Client($this->sid, $this->token);
    $this->assertEquals($client->api->baseUrl, "https://" . $domain);
  }

  public function testThrowExceptionWithoutHostname(): void {
    unset($_ENV['SIGNALWIRE_API_HOSTNAME']);
    putenv("SIGNALWIRE_API_HOSTNAME");

    $this->expectException(Exception::class);
    $client = new Client($this->sid, $this->token);
  }

  public function testTNoExceptionIfSetInConstructor(): void {
    $domain = 'constructor.signalwire.com';
    unset($_ENV['SIGNALWIRE_API_HOSTNAME']);
    putenv("SIGNALWIRE_API_HOSTNAME");
    $client = new Client($this->sid, $this->token, $domain);
    $this->assertEquals($client->api->baseUrl, "https://" . $domain);
  }
}

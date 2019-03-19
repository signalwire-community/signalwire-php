<?php

use PHPUnit\Framework\TestCase;
use SignalWire\Rest\Client;

class ClientTest extends TestCase
{
  protected $sid = "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX";
  protected $token = "PTXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

  protected function setUp() {
    unset($_ENV[Client::ENV_SW_SPACE]);
    putenv(Client::ENV_SW_SPACE);

    unset($_ENV[Client::ENV_SW_HOSTNAME]);
    putenv(Client::ENV_SW_HOSTNAME);
  }

  public function testRestEndpointWithEnvHostname(): void {
    $domain = 'hostname.signalwire.com';
    $_ENV[Client::ENV_SW_HOSTNAME] = $domain;

    $client = new Client($this->sid, $this->token);
    $this->assertEquals($client->api->baseUrl, "https://$domain");
  }

  public function testRestEndpointWithEnvSpaceUrl(): void {
    $domain = 'space.signalwire.com';
    $_ENV[Client::ENV_SW_SPACE] = $domain;

    $client = new Client($this->sid, $this->token);
    $this->assertEquals($client->api->baseUrl, "https://$domain");
  }

  public function testRestEndpointWithPutEnvHostname(): void {
    $domain = 'test.signalwire.com';
    putenv(Client::ENV_SW_HOSTNAME . "=$domain");

    $client = new Client($this->sid, $this->token);
    $this->assertEquals($client->api->baseUrl, "https://$domain");
  }

  public function testRestEndpointWithPutEnvSpaceUrl(): void {
    $domain = 'space.signalwire.com';
    putenv(Client::ENV_SW_SPACE . "=$domain");

    $client = new Client($this->sid, $this->token);
    $this->assertEquals($client->api->baseUrl, "https://$domain");
  }

  public function testThrowExceptionWithoutHostname(): void {
    $this->expectException(Exception::class);
    $client = new Client($this->sid, $this->token);

    $this->expectException(Exception::class);
    $client = new Client($this->sid, $this->token, array('test' => 'fake'));

    $this->expectException(Exception::class);
    $client = new Client($this->sid, $this->token, array('signalwireSpaceUrl' => ''));
  }

  public function testTNoExceptionIfSetInConstructor(): void {
    $domain = 'constructor.signalwire.com';
    $opts = array(
      'signalwireSpaceUrl' => $domain
    );
    $client = new Client($this->sid, $this->token, $opts);
    $this->assertEquals($client->api->baseUrl, "https://$domain");
  }
}

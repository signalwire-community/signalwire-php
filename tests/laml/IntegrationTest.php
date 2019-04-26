<?php

use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
{
  protected $sid;
  protected $token;
  protected $domain;
  protected $client;

  protected function setUp() {
    $this->sid = "my-signalwire-sid";
    $this->token = "my-signalwire-token";
    $this->domain = 'example.signalwire.com';
    putenv("SIGNALWIRE_API_HOSTNAME=$this->domain");

    $this->client = new SignalWire\Rest\Client($this->sid, $this->token);

    \VCR\VCR::turnOn();
  }

  protected function tearDown() {
    \VCR\VCR::eject();
    \VCR\VCR::turnOff();
  }

  public function testFaxList(): void {
    \VCR\VCR::insertCassette('list_faxes');

    $faxes = $this->client->fax->v1->faxes->read();
    $this->assertEquals(count($faxes), 7);
  }

  public function testGetFax(): void {
    \VCR\VCR::insertCassette('get_fax');

    $fax = $this->client->fax->v1->faxes("831455c6-574e-4d8b-b6ee-2418140bf4cd")->fetch();
    $this->assertEquals($fax->to, '+15556677888');
  }
}

<?php
require_once dirname(__FILE__) . '/BaseActionCase.php';

use PHPUnit\Framework\TestCase;
use SignalWire\Relay\Calling\Devices;

class RelayCallingDevicesTest extends TestCase {

  public function testPhoneDevice(): void {
    $phone = new Devices\Phone(['from' => 'from', 'to' => 'to']);
    // print_r( $phone);
    // print_r( (array)$phone);
    // TODO:
    $this->assertEquals(1, 1);
  }

}

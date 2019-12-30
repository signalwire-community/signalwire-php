<?php
require_once dirname(__FILE__) . '/BaseActionCase.php';

use PHPUnit\Framework\TestCase;
use SignalWire\Relay\Calling\Devices;

class RelayCallingDevicesTest extends TestCase {

  // PHONE

  public function testPhoneDeviceWithFlattenedParams(): void {
    $phone = new Devices\Phone(['from' => '1234', 'to' => '5678']);
    $this->assertEquals($phone->type, 'phone');
    $this->assertEquals($phone->params, json_decode('{"from_number":"1234","to_number":"5678"}'));
  }

  public function testPhoneDeviceWithFlattenedParamsAndTimeout(): void {
    $phone = new Devices\Phone(['from' => '1234', 'to' => '5678', 'timeout' => 45]);
    $this->assertEquals($phone->type, 'phone');
    $this->assertEquals($phone->params, json_decode('{"from_number":"1234","to_number":"5678","timeout":"45"}'));
  }

  public function testPhoneDeviceWithNestedParams(): void {
    $options = json_decode('{"type":"phone","params":{"from_number":"1234","to_number":"5678"}}');
    $phone = new Devices\Phone($options);
    $this->assertEquals($phone->type, 'phone');
    $this->assertEquals($phone->params, json_decode('{"from_number":"1234","to_number":"5678"}'));
  }

  public function testPhoneDeviceWithNestedParamsAndTimeout(): void {
    $options = json_decode('{"type":"phone","params":{"from_number":"1234","to_number":"5678","timeout":30}}');
    $phone = new Devices\Phone($options);
    $this->assertEquals($phone->type, 'phone');
    $this->assertEquals($phone->params, json_decode('{"from_number":"1234","to_number":"5678","timeout":"30"}'));
  }

  // AGORA

  public function testAgoraDeviceWithFlattenedParams(): void {
    $agora = new Devices\Agora(['from' => '1234', 'to' => '5678', 'app_id' => 'uuid', 'channel' => '1111']);
    $this->assertEquals($agora->type, 'agora');
    $this->assertEquals($agora->params, json_decode('{"from":"1234","to":"5678","appid":"uuid","channel":"1111"}'));
  }

  public function testAgoraDeviceWithFlattenedParamsAndTimeout(): void {
    $agora = new Devices\Agora(['from' => '1234', 'to' => '5678', 'app_id' => 'uuid', 'channel' => '1111', 'timeout' => 45]);
    $this->assertEquals($agora->type, 'agora');
    $this->assertEquals($agora->params, json_decode('{"from":"1234","to":"5678","appid":"uuid","channel":"1111","timeout":"45"}'));
  }

  public function testAgoraDeviceWithNestedParams(): void {
    $options = json_decode('{"type":"agora","params":{"from":"1234","to":"5678","appid":"uuid","channel":"1111"}}');
    $agora = new Devices\Agora($options);
    $this->assertEquals($agora->type, 'agora');
    $this->assertEquals($agora->params, json_decode('{"from":"1234","to":"5678","appid":"uuid","channel":"1111"}'));
  }

  public function testAgoraDeviceWithNestedParamsAndTimeout(): void {
    $options = json_decode('{"type":"agora","params":{"from":"1234","to":"5678","appid":"uuid","channel":"1111","timeout":30}}');
    $agora = new Devices\Agora($options);
    $this->assertEquals($agora->type, 'agora');
    $this->assertEquals($agora->params, json_decode('{"from":"1234","to":"5678","appid":"uuid","channel":"1111","timeout":"30"}'));
  }

  // SIP

  public function testSipDeviceWithFlattenedParams(): void {
    $headers = [
      'X-account-id' => '1234',
      'X-account-foo' => 'baz'
    ];
    $sip = new Devices\Sip(['from' => 'user@somewhere.com', 'to' => 'user@example.com', 'headers' => $headers]);
    $this->assertEquals($sip->type, 'sip');
    $this->assertEquals($sip->params, json_decode('{"from":"user@somewhere.com","to":"user@example.com","headers":{"X-account-id":"1234","X-account-foo":"baz"}}'));
  }

  public function testSipDeviceWithFlattenedParamsAndCodecs(): void {
    $sip = new Devices\Sip(['from' => 'user@somewhere.com', 'to' => 'user@example.com', 'codecs' => ['c1', 'c2']]);
    $this->assertEquals($sip->type, 'sip');
    $this->assertEquals($sip->params, json_decode('{"from":"user@somewhere.com","to":"user@example.com","codecs":["c1","c2"]}'));
  }

  public function testSipDeviceWithFlattenedParamsAndTimeout(): void {
    $sip = new Devices\Sip(['from' => 'user@somewhere.com', 'to' => 'user@example.com', 'timeout' => 45]);
    $this->assertEquals($sip->type, 'sip');
    $this->assertEquals($sip->params, json_decode('{"from":"user@somewhere.com","to":"user@example.com","timeout":"45"}'));
  }

  public function testSipDeviceWithNestedParams(): void {
    $options = json_decode('{"type":"sip","params":{"from":"user@somewhere.com","to":"user@example.com","timeout":30,"headers":{"X-account-id":"1234","X-account-foo":"bar"}}}');
    $sip = new Devices\Sip($options);
    $this->assertEquals($sip->type, 'sip');
    $this->assertEquals($sip->params, json_decode('{"from":"user@somewhere.com","to":"user@example.com","timeout":30,"headers":{"X-account-id":"1234","X-account-foo":"bar"}}'));
  }

  public function testSipDeviceWithNestedParamsAndWebrtcMedia(): void {
    $options = json_decode('{"type":"sip","params":{"from":"user@somewhere.com","to":"user@example.com","webrtc_media":true,"headers":{"X-account-id":"1234","X-account-foo":"bar"}}}');
    $sip = new Devices\Sip($options);
    $this->assertEquals($sip->type, 'sip');
    $this->assertEquals($sip->params, json_decode('{"from":"user@somewhere.com","to":"user@example.com","webrtc_media":true,"headers":{"X-account-id":"1234","X-account-foo":"bar"}}'));
  }

  public function testSipDeviceWithNestedParamsAndTimeout(): void {
    $options = json_decode('{"type":"sip","params":{"from":"user@somewhere.com","to":"user@example.com","timeout":30,"headers":{"X-account-id":"1234","X-account-foo":"bar"}}}');
    $sip = new Devices\Sip($options);
    $this->assertEquals($sip->type, 'sip');
    $this->assertEquals($sip->params, json_decode('{"from":"user@somewhere.com","to":"user@example.com","timeout":30,"headers":{"X-account-id":"1234","X-account-foo":"bar"}}'));
  }

  // WebRTC

  public function testWebRTCDeviceWithFlattenedParams(): void {
    $webrtc = new Devices\WebRTC(['from' => 'user@somewhere.com', 'to' => 'user@example.com', 'timeout' => 45]);
    $this->assertEquals($webrtc->type, 'webrtc');
    $this->assertEquals($webrtc->params, json_decode('{"from":"user@somewhere.com","to":"user@example.com","timeout":45}'));
  }

  public function testWebRTCDeviceWithFlattenedParamsAndCodecs(): void {
    $webrtc = new Devices\WebRTC(['from' => 'user@somewhere.com', 'to' => 'user@example.com', 'codecs' => ['c1', 'c2']]);
    $this->assertEquals($webrtc->type, 'webrtc');
    $this->assertEquals($webrtc->params, json_decode('{"from":"user@somewhere.com","to":"user@example.com","codecs":["c1","c2"]}'));
  }

  public function testWebRTCDeviceWithFlattenedParamsAndTimeout(): void {
    $webrtc = new Devices\WebRTC(['from' => 'user@somewhere.com', 'to' => 'user@example.com', 'timeout' => 45]);
    $this->assertEquals($webrtc->type, 'webrtc');
    $this->assertEquals($webrtc->params, json_decode('{"from":"user@somewhere.com","to":"user@example.com","timeout":"45"}'));
  }

  public function testWebRTCDeviceWithNestedParams(): void {
    $options = json_decode('{"type":"webrtc","params":{"from":"user@webrtc.signalwire.com","to":"3500@1a2b3c4d.conference.signalwire.com","timeout":30}}');
    $webrtc = new Devices\WebRTC($options);
    $this->assertEquals($webrtc->type, 'webrtc');
    $this->assertEquals($webrtc->params, json_decode('{"from":"user@webrtc.signalwire.com","to":"3500@1a2b3c4d.conference.signalwire.com","timeout":30}'));
  }

  public function testWebRTCDeviceWithNestedParamsAndCodecs(): void {
    $options = json_decode('{"type":"webrtc","params":{"from":"user@webrtc.signalwire.com","to":"3500@1a2b3c4d.conference.signalwire.com","codecs":["c1","c2"]}}');
    $webrtc = new Devices\WebRTC($options);
    $this->assertEquals($webrtc->type, 'webrtc');
    $this->assertEquals($webrtc->params, json_decode('{"from":"user@webrtc.signalwire.com","to":"3500@1a2b3c4d.conference.signalwire.com","codecs":["c1","c2"]}'));
  }

  public function testWebRTCDeviceWithNestedParamsAndTimeout(): void {
    $options = json_decode('{"type":"webrtc","params":{"from":"user@webrtc.signalwire.com","to":"3500@1a2b3c4d.conference.signalwire.com","timeout":30}}');
    $webrtc = new Devices\WebRTC($options);
    $this->assertEquals($webrtc->type, 'webrtc');
    $this->assertEquals($webrtc->params, json_decode('{"from":"user@webrtc.signalwire.com","to":"3500@1a2b3c4d.conference.signalwire.com","timeout":30}'));
  }

}

<?php

use PHPUnit\Framework\TestCase;
use SignalWire\Messages\Connect;
use SignalWire\Messages\Execute;
use SignalWire\Messages\Subscription;

class MessagesTest extends TestCase
{
  const UUID = '6118cfc7-9192-4869-884a-052d53434e4c';

  public function testBladeConnectWithoutSessionId(): void {
    $msg = new Connect('project', 'token');
    $json = '{"jsonrpc":"2.0","id":"'.$msg->id.'","method":"blade.connect","params":{"version":{"major":2,"minor":1,"revision":0},"authentication":{"project":"project","token":"token"},"agent":"PHP SDK/'.\SignalWire\VERSION.'"}}';
    $this->assertEquals($msg->toJson(), $json);
  }

  public function testBladeConnectWithSessionId(): void {
    $msg = new Connect('project', 'token', 'sessId');
    $json = '{"jsonrpc":"2.0","id":"'.$msg->id.'","method":"blade.connect","params":{"version":{"major":2,"minor":1,"revision":0},"authentication":{"project":"project","token":"token"},"agent":"PHP SDK/'.\SignalWire\VERSION.'","sessionid":"sessId"}}';
    $this->assertEquals($msg->toJson(), $json);
  }

  public function testBladeExecuteRequest(): void {
    $params = array(
      'key' => 'value',
      'nested' => array('service' => 'test')
    );
    $msg = new Execute($params);
    $json = '{"jsonrpc":"2.0","id":"'.$msg->id.'","method":"blade.execute","params":{"key":"value","nested":{"service":"test"}}}';
    $this->assertEquals($msg->toJson(), $json);

    $params = array(
      'key' => 'value',
      'params' => array('channels' => array('test', 'test1', 'test2')),
    );
    $msg = new Execute($params);
    $json = '{"jsonrpc":"2.0","id":"'.$msg->id.'","method":"blade.execute","params":{"key":"value","params":{"channels":["test","test1","test2"]}}}';
    $this->assertEquals($msg->toJson(), $json);
  }

  public function testBladeSubscription(): void {
    $params = array(
      'command' => 'add',
      'protocol' => 'test',
      'channels' => array('c1', 'c2', 'c3')
    );
    $msg = new Subscription($params);
    $json = '{"jsonrpc":"2.0","id":"'.$msg->id.'","method":"blade.subscription","params":{"command":"add","protocol":"test","channels":["c1","c2","c3"]}}';
    $this->assertEquals($msg->toJson(), $json);

    $params = array(
      'command' => 'remove',
      'protocol' => 'test',
      'channels' => array('c1', 'c2', 'c3')
    );
    $msg = new Subscription($params);
    $json = '{"jsonrpc":"2.0","id":"'.$msg->id.'","method":"blade.subscription","params":{"command":"remove","protocol":"test","channels":["c1","c2","c3"]}}';
    $this->assertEquals($msg->toJson(), $json);
  }
}

<?php

use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
  const EVENT_NAME = 'event-test';
  const UNIQUE_ID = '6118cfc7-9192-4869-884a-052d53434e4c';
  protected $mock;

  public function noop(): void {}

  protected function setUp() {
    $this->mock = $this->getMockBuilder(self::class)
      ->enableProxyingToOriginalMethods()
      ->getMock();
  }

  protected function tearDown() {
    SignalWire\Handler::clear();
  }

  // register()
  public function testRegisterWithoutUniqueId(): void {
    SignalWire\Handler::register(self::EVENT_NAME, [$this->mock, 'noop']);

    $this->assertTrue(SignalWire\Handler::isQueued(self::EVENT_NAME));
    $this->assertFalse(SignalWire\Handler::isQueued(self::EVENT_NAME, self::UNIQUE_ID));
    $this->assertEquals(SignalWire\Handler::queueCount(self::EVENT_NAME), 1);
  }

  public function testRegisterWithUniqueId(): void {
    SignalWire\Handler::register(self::EVENT_NAME, [$this->mock, 'noop'], self::UNIQUE_ID);

    $this->assertTrue(SignalWire\Handler::isQueued(self::EVENT_NAME, self::UNIQUE_ID));
    $this->assertFalse(SignalWire\Handler::isQueued(self::EVENT_NAME));
    $this->assertEquals(SignalWire\Handler::queueCount(self::EVENT_NAME, self::UNIQUE_ID), 1);
  }

  // registerOnce()
  public function testRegisterOnceWithoutUniqueId(): void {
    SignalWire\Handler::registerOnce(self::EVENT_NAME, [$this->mock, 'noop']);

    $this->assertTrue(SignalWire\Handler::isQueued(self::EVENT_NAME));
    $this->assertFalse(SignalWire\Handler::isQueued(self::EVENT_NAME, self::UNIQUE_ID));
    $this->assertEquals(SignalWire\Handler::queueCount(self::EVENT_NAME), 1);

    $this->mock->expects($this->exactly(1))->method('noop')->with('once');

    SignalWire\Handler::trigger(self::EVENT_NAME, 'once');
    SignalWire\Handler::trigger(self::EVENT_NAME, 'once');

    $this->assertFalse(SignalWire\Handler::isQueued(self::EVENT_NAME));
  }

  public function testRegisterOnceWithUniqueId(): void {
    SignalWire\Handler::registerOnce(self::EVENT_NAME, [$this->mock, 'noop'], self::UNIQUE_ID);

    $this->assertTrue(SignalWire\Handler::isQueued(self::EVENT_NAME, self::UNIQUE_ID));
    $this->assertFalse(SignalWire\Handler::isQueued(self::EVENT_NAME));
    $this->assertEquals(SignalWire\Handler::queueCount(self::EVENT_NAME, self::UNIQUE_ID), 1);

    $this->mock->expects($this->exactly(1))->method('noop')->with('once');

    SignalWire\Handler::trigger(self::EVENT_NAME, 'once', self::UNIQUE_ID);
    SignalWire\Handler::trigger(self::EVENT_NAME, 'once', self::UNIQUE_ID);

    $this->assertFalse(SignalWire\Handler::isQueued(self::EVENT_NAME, self::UNIQUE_ID));
  }

  // deRegister()
  public function testDeRegisterWithoutUniqueId(): void {
    SignalWire\Handler::register(self::EVENT_NAME, [$this->mock, 'noop']);

    $this->assertTrue(SignalWire\Handler::isQueued(self::EVENT_NAME));
    SignalWire\Handler::deRegister(self::EVENT_NAME, [$this->mock, 'noop']);
    $this->assertFalse(SignalWire\Handler::isQueued(self::EVENT_NAME));
  }

  public function testDeRegisterWithUniqueId(): void {
    SignalWire\Handler::register(self::EVENT_NAME, [$this->mock, 'noop'], self::UNIQUE_ID);

    $this->assertTrue(SignalWire\Handler::isQueued(self::EVENT_NAME, self::UNIQUE_ID));
    SignalWire\Handler::deRegister(self::EVENT_NAME, [$this->mock, 'noop'], self::UNIQUE_ID);
    $this->assertFalse(SignalWire\Handler::isQueued(self::EVENT_NAME, self::UNIQUE_ID));
  }

  // deRegisterAll()
  public function testDeRegisterAllWithoutUniqueId(): void {
    SignalWire\Handler::register(self::EVENT_NAME, [$this->mock, 'noop']);
    SignalWire\Handler::register(self::EVENT_NAME, [$this->mock, 'noop'], self::UNIQUE_ID);

    SignalWire\Handler::deRegisterAll(self::EVENT_NAME);
    $this->assertFalse(SignalWire\Handler::isQueued(self::EVENT_NAME));
    $this->assertFalse(SignalWire\Handler::isQueued(self::EVENT_NAME, self::UNIQUE_ID));
  }

  // trigger()
  public function testTriggerWithoutUniqueId(): void {
    SignalWire\Handler::register(self::EVENT_NAME, [$this->mock, 'noop']);

    $this->mock->expects($this->exactly(2))->method('noop')->with('hello');

    SignalWire\Handler::trigger(self::EVENT_NAME, 'hello');
    SignalWire\Handler::trigger(self::EVENT_NAME, 'hello');
  }

  public function testTriggerWithUniqueId(): void {
    SignalWire\Handler::register(self::EVENT_NAME, [$this->mock, 'noop'], self::UNIQUE_ID);

    $this->mock->expects($this->exactly(2))->method('noop')->with('unique');

    SignalWire\Handler::trigger(self::EVENT_NAME, 'unique', self::UNIQUE_ID);
    SignalWire\Handler::trigger(self::EVENT_NAME, 'unique', self::UNIQUE_ID);
  }
}

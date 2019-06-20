<?php
require_once dirname(__FILE__) . '/BaseActionCase.php';

use PHPUnit\Framework\TestCase;
use SignalWire\Relay\Calling\Blocker;

class BlockerTest extends TestCase
{
  const UUID = 'e36f227c-2946-11e8-b467-0ed5f89f718b';

  protected function setUp() {
    $this->mockUuid();
  }

  protected function tearDown() {
    \Ramsey\Uuid\Uuid::setFactory(new \Ramsey\Uuid\UuidFactory());
  }

  protected function mockUuid() {
    $factory = $this->createMock(\Ramsey\Uuid\UuidFactoryInterface::class);
    $factory->method('uuid4')
      ->will($this->returnValue(\Ramsey\Uuid\Uuid::fromString(self::UUID)));
    \Ramsey\Uuid\Uuid::setFactory($factory);
  }

  public function testBlockerExposeControlId(): void {
    $blocker = new Blocker('event', function($params) {});
    $this->assertEquals($blocker->controlId, self::UUID);
  }

  public function testBlockerExposeEventType(): void {
    $blocker = new Blocker('event', function($params) {});
    $this->assertEquals($blocker->eventType, 'event');
  }

  public function testBlockerResolve(): void {
    $blocker = new Blocker('event', function($params) use (&$blocker) {
      ($blocker->resolve)($params);
    });
    ($blocker->resolver)('done');
    $blocker->promise->done(function($res) {
      $this->assertEquals($res, 'done');
    });
  }

  public function testBlockerRejectException(): void {
    $blocker = new Blocker('event', function($params) use (&$blocker) {
      ($blocker->reject)($params);
    });
    ($blocker->resolver)('done');
    $this->expectException(Exception::class);
    $blocker->promise->done();
  }

  public function testBlockerRejectCatch(): void {
    $blocker = new Blocker('event', function($params) use (&$blocker) {
      ($blocker->reject)($params);
    });
    ($blocker->resolver)('catch this');
    $blocker->promise->done(null, function($error){
      $this->assertEquals($error, 'catch this');
    });
  }
}

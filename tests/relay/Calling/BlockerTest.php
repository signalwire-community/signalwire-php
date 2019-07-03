<?php

use PHPUnit\Framework\TestCase;
use SignalWire\Relay\Calling\Blocker;

class BlockerTest extends TestCase
{
  const UUID = 'e36f227c-2946-11e8-b467-0ed5f89f718b';

  protected function tearDown() {
    \Ramsey\Uuid\Uuid::setFactory(new \Ramsey\Uuid\UuidFactory());
  }

  public function testBlockerExposeControlId(): void {
    $blocker = new Blocker('event', 'control-id');
    $this->assertEquals($blocker->controlId, 'control-id');
  }

  public function testBlockerExposeEventType(): void {
    $blocker = new Blocker('event', 'control-id');
    $this->assertEquals($blocker->eventType, 'event');
  }

  public function testBlockerResolve(): void {
    $blocker = new Blocker('event', 'control-id');
    ($blocker->resolve)('done');
    $blocker->promise->done(function($res) {
      $this->assertEquals($res, 'done');
    });
  }
}

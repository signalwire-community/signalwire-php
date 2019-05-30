<?php
require_once dirname(__FILE__) . '/BaseActionCase.php';

use PHPUnit\Framework\TestCase;
use SignalWire\Relay\Calling\Blocker;

class BlockerTest extends TestCase
{
  public function testBlockerExposeControlId(): void {
    $blocker = new Blocker('uuid', 'event', function($params) {});
    $this->assertEquals($blocker->controlId, 'uuid');
  }

  public function testBlockerExposeEventType(): void {
    $blocker = new Blocker('uuid', 'event', function($params) {});
    $this->assertEquals($blocker->eventType, 'event');
  }

  public function testBlockerResolve(): void {
    $blocker = new Blocker('uuid', 'event', function($params) use (&$blocker) {
      ($blocker->resolve)($params);
    });
    ($blocker->resolver)('done');
    $blocker->promise->done(function($res) {
      $this->assertEquals($res, 'done');
    });
  }

  public function testBlockerRejectException(): void {
    $blocker = new Blocker('uuid', 'event', function($params) use (&$blocker) {
      ($blocker->reject)($params);
    });
    ($blocker->resolver)('done');
    $this->expectException(Exception::class);
    $blocker->promise->done();
  }

  public function testBlockerRejectCatch(): void {
    $blocker = new Blocker('uuid', 'event', function($params) use (&$blocker) {
      ($blocker->reject)($params);
    });
    ($blocker->resolver)('catch this');
    $blocker->promise->done(null, function($error){
      $this->assertEquals($error, 'catch this');
    });
  }
}

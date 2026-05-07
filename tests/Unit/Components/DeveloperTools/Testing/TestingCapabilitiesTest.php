<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DeveloperTools\Testing;

use Avax\Components\DeveloperTools\Testing\System\Capabilities\Fakes\EventFake;
use PHPUnit\Framework\TestCase;
use stdClass;

final class TestingCapabilitiesTest extends TestCase
{
    public function test_event_fake_tracks_events() : void
    {
        $fake  = new EventFake();
        $event = new stdClass();

        $fake->dispatch($event);

        $this->assertTrue($fake->hasDispatched($event::class));
        $this->assertSame(1, $fake->dispatchedCount($event::class));
    }
}

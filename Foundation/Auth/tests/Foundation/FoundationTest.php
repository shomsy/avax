<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Foundation;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Foundation\Clock;
use Avax\Auth\System\Foundation\IdGenerator;

/**
 * Unit test for Foundation primitives.
 */
class FoundationTest extends TestCase
{
    public function testClockReturnsDateTime() : void
    {
        $clock = new Clock();
        $this->assertInstanceOf(\DateTimeImmutable::class, $clock->now());
    }

    public function testIdGeneratorGeneratesPositiveInt() : void
    {
        $generator = new IdGenerator();
        $id = $generator->generate();
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
    }
}

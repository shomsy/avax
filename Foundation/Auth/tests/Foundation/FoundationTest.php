<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Foundation;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Foundation\Clock;
use Avax\Auth\System\Foundation\IdGenerator;
use Random\RandomException;

/**
 * Unit test for Foundation primitives.
 */
class FoundationTest extends TestCase
{
    public function testClockReturnsDateTime() : void
    {
        $clock = new Clock();
        $this->assertInstanceOf(expected: DateTimeImmutable::class, actual: $clock->now());
    }

    /**
     * @throws RandomException
     */
    public function testIdGeneratorGeneratesPositiveInt() : void
    {
        $generator = new IdGenerator();
        $id = $generator->generate();
        $this->assertIsInt(actual: $id);
        $this->assertGreaterThan(expected: 0, actual: $id);
    }
}

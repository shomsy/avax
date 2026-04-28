<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Foundation;

use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Auth\System\Foundation\IdGenerator;
use Avax\Tests\TestCase;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
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
        $id        = $generator->generate();
        $this->assertIsInt(actual: $id);
        $this->assertGreaterThan(minimum: 0, actual: $id);
    }
}

<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Auth;

use Avax\Components\Identity\Auth\System\Foundation\Clock;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AuthClockCharacterizationTest extends TestCase
{
    #[Test]
    public function timestampUsesNowSource(): void
    {
        $clock = new class extends Clock {
            public function now() : DateTimeImmutable
            {
                return new DateTimeImmutable('2026-05-21 12:00:00');
            }
        };

        self::assertSame($clock->now()->getTimestamp(), $clock->timestamp());
    }
}

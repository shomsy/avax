<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Auth;

use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\InMemoryLoginRateLimitStorage;
use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\LoginRateLimit;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class LoginRateLimitClockCharacterizationTest extends TestCase
{
    #[Test]
    public function failedAttemptTimestampUsesInjectedClock(): void
    {
        $clock = new class(new DateTimeImmutable('2026-05-21 12:00:00')) extends Clock {
            public function __construct(public DateTimeImmutable $now) {}

            public function now() : DateTimeImmutable
            {
                return $this->now;
            }
        };

        $storage = new InMemoryLoginRateLimitStorage();
        $rateLimit = new LoginRateLimit(
            loginRateLimitStorage: $storage,
            clock                : $clock,
        );

        $rateLimit->recordFailed(identifier: 'USER@example.test');

        self::assertSame($clock->now->getTimestamp(), $storage->getLastAttemptTime('user@example.test'));
    }
}

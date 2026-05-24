<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Auth\Login\RateLimit;

use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\InMemoryLoginRateLimitStorage;
use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\LoginRateLimit;
use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\LoginRateLimitStorageInterface;
use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\RateLimitException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class LoginRateLimitTest extends TestCase
{
    private LoginRateLimitStorageInterface&\PHPUnit\Framework\MockObject\MockObject $storage;
    private Clock&\PHPUnit\Framework\MockObject\MockObject $clock;

    protected function setUp(): void
    {
        $this->storage = $this->createMock(LoginRateLimitStorageInterface::class);
        $this->clock = $this->createMock(Clock::class);
    }

    #[Test]
    public function test_it_throws_rate_limit_exception_when_max_attempts_exceeded(): void
    {
        $rateLimit = new LoginRateLimit($this->storage, $this->clock, maxAttempts: 3, decaySeconds: 60);

        $this->storage->method('get')->willReturn(3);
        $this->storage->method('getLastAttemptTime')->willReturn(time() - 10);

        $this->clock->method('now')
            ->willReturn(new \DateTimeImmutable('@' . time()));

        $this->expectException(RateLimitException::class);

        $rateLimit->check('milos@example.com');
    }

    #[Test]
    public function test_it_resets_attempts_after_decay_period(): void
    {
        $rateLimit = new LoginRateLimit($this->storage, $this->clock, maxAttempts: 3, decaySeconds: 60);

        $this->storage->method('get')->willReturn(5);
        $this->storage->method('getLastAttemptTime')->willReturn(time() - 120);

        $this->clock->method('now')
            ->willReturn(new \DateTimeImmutable('@' . time()));

        $this->storage->expects(self::once())
            ->method('reset')
            ->with('milos@example.com');

        $rateLimit->check('milos@example.com');
    }

    #[Test]
    public function test_it_records_failed_attempt_on_wrong_password(): void
    {
        $storage = new InMemoryLoginRateLimitStorage();
        $clock = new class extends Clock {
            public function now(): \DateTimeImmutable
            {
                return new \DateTimeImmutable();
            }
        };
        $rateLimit = new LoginRateLimit($storage, $clock, maxAttempts: 3, decaySeconds: 60);

        $rateLimit->recordFailed('milos@example.com');
        $rateLimit->recordFailed('milos@example.com');

        self::assertSame(2, $storage->get('milos@example.com'));
    }
}

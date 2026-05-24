<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Auth\Login\RateLimit;

use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\InMemoryLoginRateLimitStorage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InMemoryLoginRateLimitStorageTest extends TestCase
{
    #[Test]
    public function test_it_returns_zero_for_unknown_identifier(): void
    {
        $storage = new InMemoryLoginRateLimitStorage();
        self::assertSame(0, $storage->get('unknown@example.com'));
    }

    #[Test]
    public function test_it_increments_attempt_count(): void
    {
        $storage = new InMemoryLoginRateLimitStorage();

        $storage->increment('milos@example.com');
        $storage->increment('milos@example.com');

        self::assertSame(2, $storage->get('milos@example.com'));
    }

    #[Test]
    public function test_it_resets_attempt_count(): void
    {
        $storage = new InMemoryLoginRateLimitStorage();

        $storage->increment('milos@example.com');
        $storage->increment('milos@example.com');
        $storage->reset('milos@example.com');

        self::assertSame(0, $storage->get('milos@example.com'));
    }

    #[Test]
    public function test_it_tracks_last_attempt_time(): void
    {
        $storage = new InMemoryLoginRateLimitStorage();

        $before = time();
        $storage->increment('milos@example.com');
        $after = time();

        $lastTime = $storage->getLastAttemptTime('milos@example.com');

        self::assertGreaterThanOrEqual($before, $lastTime);
        self::assertLessThanOrEqual($after, $lastTime);
    }
}

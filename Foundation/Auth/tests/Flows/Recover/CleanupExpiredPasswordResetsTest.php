<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Recover;

use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\Recover\CleanupExpiredPasswordResets\CleanupExpiredPasswordResets;
use Avax\Auth\System\Flow\Recover\InMemoryPasswordResetStore;
use Avax\Auth\System\Foundation\Clock;
use PHPUnit\Framework\TestCase;

final class CleanupExpiredPasswordResetsTest extends TestCase
{
    public function testCleanupRemovesExpiredPasswordResets() : void
    {
        $store = new InMemoryPasswordResetStore();
        $store->issue(userId: new UserId(value: 1), expiresAt: new \DateTimeImmutable(datetime: '-1 minute'));
        $store->issue(userId: new UserId(value: 2), expiresAt: new \DateTimeImmutable(datetime: '+10 minutes'));

        $removed = (new CleanupExpiredPasswordResets(passwordResetStore: $store, clock: new Clock()))->execute();

        $this->assertSame(expected: 1, actual: $removed);
    }
}

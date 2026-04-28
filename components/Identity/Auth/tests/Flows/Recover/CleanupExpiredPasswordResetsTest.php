<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Flows\Recover;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\CleanupExpiredPasswordResets\CleanupExpiredPasswordResets;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\InMemoryPasswordResetStore;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Tests\TestCase;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

final class CleanupExpiredPasswordResetsTest extends TestCase
{
    /**
     * @throws RandomException
     */
    public function testCleanupRemovesExpiredPasswordResets() : void
    {
        $store = new InMemoryPasswordResetStore();
        $store->issue(userId: new UserId(value: 1), expiresAt: new DateTimeImmutable(datetime: '-1 minute'));
        $store->issue(userId: new UserId(value: 2), expiresAt: new DateTimeImmutable(datetime: '+10 minutes'));

        $removed = new CleanupExpiredPasswordResets(passwordResetStore: $store, clock: new Clock())->execute();

        $this->assertSame(expected: 1, actual: $removed);
    }
}

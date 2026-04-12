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
        $store->issue(new UserId(1), new \DateTimeImmutable('-1 minute'));
        $store->issue(new UserId(2), new \DateTimeImmutable('+10 minutes'));

        $removed = (new CleanupExpiredPasswordResets($store, new Clock()))->execute();

        $this->assertSame(1, $removed);
    }
}

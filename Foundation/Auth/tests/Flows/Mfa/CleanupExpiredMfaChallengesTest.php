<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Mfa;

use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\Mfa\Challenge\CleanupExpiredMfaChallenges\CleanupExpiredMfaChallenges;
use Avax\Auth\System\Flow\Mfa\Challenge\InMemoryMfaChallengeStore;
use Avax\Auth\System\Flow\Mfa\Challenge\MfaChallengeRecord;
use Avax\Auth\System\Flow\Mfa\MfaChallengePurpose;
use Avax\Auth\System\Foundation\Clock;
use PHPUnit\Framework\TestCase;

final class CleanupExpiredMfaChallengesTest extends TestCase
{
    public function testCleanupRemovesExpiredMfaChallenges() : void
    {
        $store = new InMemoryMfaChallengeStore();
        $store->issue(new MfaChallengeRecord(
            challengeId: 'expired',
            userId     : new UserId(1),
            purpose    : MfaChallengePurpose::LOGIN,
            createdAt  : new \DateTimeImmutable('-10 minutes'),
            expiresAt  : new \DateTimeImmutable('-5 minutes')
        ));
        $store->issue(new MfaChallengeRecord(
            challengeId: 'active',
            userId     : new UserId(1),
            purpose    : MfaChallengePurpose::LOGIN,
            createdAt  : new \DateTimeImmutable(),
            expiresAt  : new \DateTimeImmutable('+5 minutes')
        ));

        $removed = (new CleanupExpiredMfaChallenges($store, new Clock()))->execute();

        $this->assertSame(1, $removed);
        $this->assertNull($store->find('expired'));
        $this->assertNotNull($store->find('active'));
    }
}

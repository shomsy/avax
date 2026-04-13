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
        $store->issue(record: new MfaChallengeRecord(
            challengeId: 'expired',
            userId     : new UserId(value: 1),
            purpose    : MfaChallengePurpose::LOGIN,
            createdAt  : new \DateTimeImmutable(datetime: '-10 minutes'),
            expiresAt  : new \DateTimeImmutable(datetime: '-5 minutes')
        ));
        $store->issue(record: new MfaChallengeRecord(
            challengeId: 'active',
            userId     : new UserId(value: 1),
            purpose    : MfaChallengePurpose::LOGIN,
            createdAt  : new \DateTimeImmutable(),
            expiresAt  : new \DateTimeImmutable(datetime: '+5 minutes')
        ));

        $removed = (new CleanupExpiredMfaChallenges(challengeStore: $store, clock: new Clock()))->execute();

        $this->assertSame(expected: 1, actual: $removed);
        $this->assertNull(actual: $store->find(challengeId: 'expired'));
        $this->assertNotNull(actual: $store->find(challengeId: 'active'));
    }
}

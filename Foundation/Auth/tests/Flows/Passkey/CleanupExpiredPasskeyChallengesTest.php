<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Passkey;

use Avax\Auth\System\Capability\Passkey\InMemoryPasskeyChallengeStore;
use Avax\Auth\System\Capability\Passkey\PasskeyChallengePurpose;
use Avax\Auth\System\Capability\Passkey\PasskeyChallengeRecord;
use Avax\Auth\System\Flow\Passkey\CleanupExpiredPasskeyChallenges\CleanupExpiredPasskeyChallenges;
use Avax\Auth\System\Foundation\Clock;
use PHPUnit\Framework\TestCase;

final class CleanupExpiredPasskeyChallengesTest extends TestCase
{
    public function testCleanupRemovesExpiredAndUsedPasskeyChallenges() : void
    {
        $store = new InMemoryPasskeyChallengeStore();
        $store->issue(new PasskeyChallengeRecord(
            challengeId: 'expired',
            challenge  : 'expired-challenge',
            purpose    : PasskeyChallengePurpose::AUTHENTICATION,
            expiresAt  : new \DateTimeImmutable('-1 minute')
        ));
        $store->issue(new PasskeyChallengeRecord(
            challengeId: 'used',
            challenge  : 'used-challenge',
            purpose    : PasskeyChallengePurpose::REGISTRATION,
            expiresAt  : new \DateTimeImmutable('+10 minutes'),
            userId     : 1,
            usedAt     : new \DateTimeImmutable()
        ));
        $store->issue(new PasskeyChallengeRecord(
            challengeId: 'active',
            challenge  : 'active-challenge',
            purpose    : PasskeyChallengePurpose::AUTHENTICATION,
            expiresAt  : new \DateTimeImmutable('+10 minutes')
        ));

        $removed = (new CleanupExpiredPasskeyChallenges($store, new Clock()))->execute();

        $this->assertSame(2, $removed);
        $this->assertNull($store->find('expired'));
        $this->assertNull($store->find('used'));
        $this->assertNotNull($store->find('active'));
    }
}

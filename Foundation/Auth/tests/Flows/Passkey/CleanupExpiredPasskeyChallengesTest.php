<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\Passkey;

use Avax\Auth\System\Capabilities\Passkey\InMemoryPasskeyChallengeStore;
use Avax\Auth\System\Capabilities\Passkey\PasskeyChallengePurpose;
use Avax\Auth\System\Capabilities\Passkey\PasskeyChallengeRecord;
use Avax\Auth\System\Flows\Passkey\CleanupExpiredPasskeyChallenges\CleanupExpiredPasskeyChallenges;
use Avax\Auth\System\Foundation\Clock;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class CleanupExpiredPasskeyChallengesTest extends TestCase
{
    public function testCleanupRemovesExpiredAndUsedPasskeyChallenges() : void
    {
        $store = new InMemoryPasskeyChallengeStore();
        $store->issue(record: new PasskeyChallengeRecord(
                                  challengeId: 'expired',
                                  challenge  : 'expired-challenge',
                                  purpose    : PasskeyChallengePurpose::AUTHENTICATION,
                                  expiresAt  : new DateTimeImmutable(datetime: '-1 minute')
                              ));
        $store->issue(record: new PasskeyChallengeRecord(
                                  challengeId: 'used',
                                  challenge  : 'used-challenge',
                                  purpose    : PasskeyChallengePurpose::REGISTRATION,
                                  expiresAt  : new DateTimeImmutable(datetime: '+10 minutes'),
                                  userId     : 1,
                                  usedAt     : new DateTimeImmutable()
                              ));
        $store->issue(record: new PasskeyChallengeRecord(
                                  challengeId: 'active',
                                  challenge  : 'active-challenge',
                                  purpose    : PasskeyChallengePurpose::AUTHENTICATION,
                                  expiresAt  : new DateTimeImmutable(datetime: '+10 minutes')
                              ));

        $removed = (new CleanupExpiredPasskeyChallenges(challengeStore: $store, clock: new Clock()))->execute();

        $this->assertSame(expected: 2, actual: $removed);
        $this->assertNull(actual: $store->find(challengeId: 'expired'));
        $this->assertNull(actual: $store->find(challengeId: 'used'));
        $this->assertNotNull(actual: $store->find(challengeId: 'active'));
    }
}

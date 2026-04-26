<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\Token;

use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\InMemoryRefreshTokenStore;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Tests\TestCase;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

final class InMemoryRefreshTokenStoreTest extends TestCase
{
    /**
     * @throws RandomException
     */
    public function testIssueFindAndRotateRefreshTokenRecords() : void
    {
        $store  = new InMemoryRefreshTokenStore();
        $userId = new UserId(value: 42);

        $issued = $store->issue(
            userId   : $userId,
            expiresAt: new DateTimeImmutable(datetime: '+1 hour'),
            familyId : 'family-1'
        );

        $record = $store->find(plainToken: $issued->token);

        $this->assertNotNull(actual: $record);
        $this->assertSame(expected: $issued->tokenId, actual: $record?->tokenId);
        $this->assertSame(expected: 'family-1', actual: $record?->familyId);
        $this->assertFalse(condition: $record?->revoked ?? true);
        $this->assertFalse(condition: $record?->wasRotated());

        $store->markRotated(tokenId: $issued->tokenId, replacementTokenId: 'replacement-token');
        $rotated = $store->find(plainToken: $issued->token);

        $this->assertSame(expected: 'replacement-token', actual: $rotated?->replacementId);
        $this->assertTrue(condition: $rotated?->wasRotated() ?? false);
    }

    /**
     * @throws RandomException
     */
    public function testRevokeFamilyAndUserAffectOnlyMatchingRecords() : void
    {
        $store = new InMemoryRefreshTokenStore();

        $familyUserOne = $store->issue(
            userId   : new UserId(value: 1),
            expiresAt: new DateTimeImmutable(datetime: '+1 hour'),
            familyId : 'family-1'
        );
        $familyUserTwo = $store->issue(
            userId   : new UserId(value: 2),
            expiresAt: new DateTimeImmutable(datetime: '+1 hour'),
            familyId : 'family-1'
        );
        $otherFamily   = $store->issue(
            userId   : new UserId(value: 1),
            expiresAt: new DateTimeImmutable(datetime: '+1 hour'),
            familyId : 'family-2'
        );

        $store->revokeFamily(familyId: 'family-1');

        $this->assertNull(actual: $store->find(plainToken: $familyUserOne->token));
        $this->assertNull(actual: $store->find(plainToken: $familyUserTwo->token));
        $this->assertNotNull(actual: $store->find(plainToken: $otherFamily->token));

        $store->revokeUser(userId: new UserId(value: 1));

        $this->assertNull(actual: $store->find(plainToken: $otherFamily->token));
    }
}

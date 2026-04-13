<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\OAuth;

use Avax\Auth\System\Capability\OAuth\InMemoryAuthorizationCodeStore;
use Avax\Auth\System\Capability\OAuth\PkceMethod;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\OAuth\CleanupExpiredAuthorizationCodes\CleanupExpiredAuthorizationCodes;
use Avax\Auth\System\Foundation\Clock;
use PHPUnit\Framework\TestCase;

final class CleanupExpiredAuthorizationCodesTest extends TestCase
{
    public function testCleanupRemovesExpiredAndUsedAuthorizationCodes() : void
    {
        $store   = new InMemoryAuthorizationCodeStore();
        $expired = $store->issue(
            userId             : new UserId(value: 1),
            clientId           : 'client-expired',
            redirectUri        : 'https://example.test/callback',
            scopes             : ['openid'],
            expiresAt          : new \DateTimeImmutable(datetime: '-1 minute'),
            codeChallenge      : 'challenge',
            codeChallengeMethod: PkceMethod::S256
        );
        $used = $store->issue(
            userId             : new UserId(value: 1),
            clientId           : 'client-used',
            redirectUri        : 'https://example.test/callback',
            scopes             : ['openid'],
            expiresAt          : new \DateTimeImmutable(datetime: '+5 minutes'),
            codeChallenge      : 'challenge',
            codeChallengeMethod: PkceMethod::S256
        );
        $active = $store->issue(
            userId             : new UserId(value: 1),
            clientId           : 'client-active',
            redirectUri        : 'https://example.test/callback',
            scopes             : ['openid'],
            expiresAt          : new \DateTimeImmutable(datetime: '+5 minutes'),
            codeChallenge      : 'challenge',
            codeChallengeMethod: PkceMethod::S256
        );
        $store->markUsed(codeId: $used->codeId, usedAt: new \DateTimeImmutable());

        $removed = (new CleanupExpiredAuthorizationCodes(codeStore: $store, clock: new Clock()))->execute();

        $this->assertSame(expected: 2, actual: $removed);
        $this->assertNull(actual: $store->find(plainCode: $expired->code));
        $this->assertNull(actual: $store->find(plainCode: $used->code));
        $this->assertNotNull(actual: $store->find(plainCode: $active->code));
    }
}

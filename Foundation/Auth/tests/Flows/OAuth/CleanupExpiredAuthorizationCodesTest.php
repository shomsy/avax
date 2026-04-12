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
            userId             : new UserId(1),
            clientId           : 'client-expired',
            redirectUri        : 'https://example.test/callback',
            scopes             : ['openid'],
            expiresAt          : new \DateTimeImmutable('-1 minute'),
            codeChallenge      : 'challenge',
            codeChallengeMethod: PkceMethod::S256
        );
        $used = $store->issue(
            userId             : new UserId(1),
            clientId           : 'client-used',
            redirectUri        : 'https://example.test/callback',
            scopes             : ['openid'],
            expiresAt          : new \DateTimeImmutable('+5 minutes'),
            codeChallenge      : 'challenge',
            codeChallengeMethod: PkceMethod::S256
        );
        $active = $store->issue(
            userId             : new UserId(1),
            clientId           : 'client-active',
            redirectUri        : 'https://example.test/callback',
            scopes             : ['openid'],
            expiresAt          : new \DateTimeImmutable('+5 minutes'),
            codeChallenge      : 'challenge',
            codeChallengeMethod: PkceMethod::S256
        );
        $store->markUsed($used->codeId, new \DateTimeImmutable());

        $removed = (new CleanupExpiredAuthorizationCodes($store, new Clock()))->execute();

        $this->assertSame(2, $removed);
        $this->assertNull($store->find($expired->code));
        $this->assertNull($store->find($used->code));
        $this->assertNotNull($store->find($active->code));
    }
}

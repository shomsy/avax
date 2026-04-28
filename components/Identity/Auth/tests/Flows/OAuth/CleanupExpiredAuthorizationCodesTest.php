<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Flows\OAuth;

use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\CleanupExpiredAuthorizationCodes\CleanupExpiredAuthorizationCodes;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\InMemoryAuthorizationCodeStore;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\PkceMethod;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Tests\TestCase;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

final class CleanupExpiredAuthorizationCodesTest extends TestCase
{
    /**
     * @throws RandomException
     */
    public function testCleanupRemovesExpiredAndUsedAuthorizationCodes() : void
    {
        $store   = new InMemoryAuthorizationCodeStore();
        $expired = $store->issue(
            userId             : new UserId(value: 1),
            clientId           : 'client-expired',
            redirectUri        : 'https://example.test/callback',
            scopes             : ['openid'],
            expiresAt          : new DateTimeImmutable(datetime: '-1 minute'),
            codeChallenge      : 'challenge',
            codeChallengeMethod: PkceMethod::S256
        );
        $used    = $store->issue(
            userId             : new UserId(value: 1),
            clientId           : 'client-used',
            redirectUri        : 'https://example.test/callback',
            scopes             : ['openid'],
            expiresAt          : new DateTimeImmutable(datetime: '+5 minutes'),
            codeChallenge      : 'challenge',
            codeChallengeMethod: PkceMethod::S256
        );
        $active  = $store->issue(
            userId             : new UserId(value: 1),
            clientId           : 'client-active',
            redirectUri        : 'https://example.test/callback',
            scopes             : ['openid'],
            expiresAt          : new DateTimeImmutable(datetime: '+5 minutes'),
            codeChallenge      : 'challenge',
            codeChallengeMethod: PkceMethod::S256
        );
        $store->markUsed(codeId: $used->codeId, usedAt: new DateTimeImmutable());

        $removed = new CleanupExpiredAuthorizationCodes(codeStore: $store, clock: new Clock())->execute();

        $this->assertSame(expected: 2, actual: $removed);
        $this->assertNull(actual: $store->find(plainCode: $expired->code));
        $this->assertNull(actual: $store->find(plainCode: $used->code));
        $this->assertNotNull(actual: $store->find(plainCode: $active->code));
    }
}

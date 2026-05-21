<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\ExternalIdentity;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\InMemoryOidcRequestObjectStore;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\OpenSslOidcProvider;
use Avax\Components\Identity\ExternalIdentity\System\Foundation\Time\Clock;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class OidcClockCharacterizationTest extends TestCase
{
    #[Test]
    public function requestObjectStoreUsesInjectedClockForCreationAndExpiry(): void
    {
        $clock = new class(new DateTimeImmutable('2026-05-21 12:00:00')) implements Clock {
            public function __construct(public DateTimeImmutable $now) {}

            public function now() : DateTimeImmutable
            {
                return $this->now;
            }
        };

        $store = new InMemoryOidcRequestObjectStore(clock: $clock);
        $object = $store->store(
            requestUri: 'urn:request:1',
            claims    : ['client_id' => 'client-1'],
            expiresAt : new DateTimeImmutable('2026-05-21 12:01:00'),
        );

        self::assertSame($clock->now->getTimestamp(), $object->createdAt->getTimestamp());
        self::assertSame($object, $store->find('urn:request:1'));

        $clock->now = new DateTimeImmutable('2026-05-21 12:01:00');

        self::assertNull($store->find('urn:request:1'));
    }

    #[Test]
    public function providerIssuesAndResolvesTokensAgainstInjectedClock(): void
    {
        $clock = new class(new DateTimeImmutable('2026-05-21 12:00:00')) implements Clock {
            public function __construct(public DateTimeImmutable $now) {}

            public function now() : DateTimeImmutable
            {
                return $this->now;
            }
        };

        $provider = new OpenSslOidcProvider(
            issuer               : 'https://issuer.example.test',
            privateKeyPem        : self::privateKeyPem(),
            keyId                : 'key-1',
            authorizationEndpoint: 'https://issuer.example.test/oauth/authorize',
            tokenEndpoint        : 'https://issuer.example.test/oauth/token',
            userInfoEndpoint     : 'https://issuer.example.test/oidc/userinfo',
            jsonWebKeySetUri     : 'https://issuer.example.test/oidc/jwks',
            clock                : $clock,
        );

        $idToken = $provider->issueIdToken(
            user    : new User(
                id          : new UserId(123),
                email       : new UserEmail('user@example.test'),
                username    : 'user',
                passwordHash: 'hash',
            ),
            clientId: 'client-1',
            scopes  : ['openid'],
        );

        $claims = $provider->resolveIdToken(idToken: $idToken->token);

        self::assertIsArray($claims);
        self::assertSame($clock->now->getTimestamp(), $claims['iat']);
        self::assertSame($clock->now->getTimestamp() + 600, $claims['exp']);

        $clock->now = new DateTimeImmutable('2026-05-21 12:10:00');

        self::assertNull($provider->resolveIdToken(idToken: $idToken->token));
    }

    private static function privateKeyPem() : string
    {
        $key = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if ($key === false || ! openssl_pkey_export(key: $key, output: $privateKeyPem)) {
            throw new RuntimeException('Unable to generate OIDC test private key.');
        }

        return $privateKeyPem;
    }
}

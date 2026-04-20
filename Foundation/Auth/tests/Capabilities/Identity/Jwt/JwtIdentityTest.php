<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capabilities\Identity\Jwt;

use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capabilities\OAuth\SenderConstraint\OAuthSenderConstraint;
use Avax\Auth\System\Capabilities\OAuth\SenderConstraint\OAuthSenderConstraintType;
use Avax\Auth\System\Capabilities\User\User;
use Avax\Auth\System\Capabilities\User\UserEmail;
use Avax\Auth\System\Capabilities\User\UserId;
use Avax\Auth\System\Capabilities\UserSource\InMemoryUserSource;
use Avax\Auth\System\Capabilities\UserSource\UserSourceInterface;
use Avax\Auth\System\Flows\Token\HmacTokenCodec;
use Avax\Auth\System\Flows\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Flows\Token\InMemoryTokenRevocationStore;
use Avax\Auth\System\Foundation\Clock;
use DateMalformedStringException;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

/**
 * Unit test for JWT identity state handling.
 */
class JwtIdentityTest extends TestCase
{
    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function testJwtIdentityIssueResolveAndRevokeCycle() : void
    {
        $user = new User(
            id          : new UserId(value: 7),
            email       : new UserEmail(value: 'jwt@example.com'),
            username    : 'jwt-user',
            passwordHash: 'hash'
        );

        $userSource = new InMemoryUserSource();
        $userSource->create(user: $user);
        $revocationStore = new InMemoryTokenRevocationStore();

        $jwt = new JwtIdentity(
            userSource       : $userSource,
            codec            : new HmacTokenCodec(secret: 'super-secret-key'),
            clock            : new Clock(),
            revocationStore  : $revocationStore,
            refreshTokenStore: new InMemoryRefreshTokenStore()
        );

        $issued   = $jwt->issue(user: $user);
        $resolved = $jwt->resolve(token: $issued->token);

        $this->assertNotNull(actual: $resolved);
        $this->assertSame(expected: $user->getId()->value, actual: $resolved?->user->getId()->value);

        $jwt->revoke(tokenId: $issued->tokenId, expiresAt: $issued->expiresAt);

        $this->assertNull(actual: $jwt->resolve(token: $issued->token));
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function testJwtIdentityRejectsInactiveUsersWhenIssuing() : void
    {
        $inactiveUser = new User(
            id          : new UserId(value: 8),
            email       : new UserEmail(value: 'inactive-issue@example.com'),
            username    : 'inactive-issue',
            passwordHash: 'hash',
            isActive    : false
        );

        $jwt = new JwtIdentity(
            userSource: Mockery::mock(UserSourceInterface::class),
            codec     : new HmacTokenCodec(secret: 'super-secret-key'),
            clock     : new Clock()
        );

        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(message: 'Inactive users cannot be authenticated.');

        $jwt->issue(user: $inactiveUser);
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function testJwtIdentityRejectsInactiveUserTokens() : void
    {
        $activeUser = new User(
            id          : new UserId(value: 9),
            email       : new UserEmail(value: 'active-jwt@example.com'),
            username    : 'active-jwt',
            passwordHash: 'hash'
        );

        $inactiveUser = new User(
            id          : new UserId(value: 9),
            email       : new UserEmail(value: 'inactive-jwt@example.com'),
            username    : 'inactive-jwt',
            passwordHash: 'hash',
            isActive    : false
        );

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findById')
            ->with(Mockery::on(closure: static fn ($id) => $id instanceof UserId && $id->value === 9))
            ->andReturn($inactiveUser);

        $jwt = new JwtIdentity(
            userSource: $userSource,
            codec     : new HmacTokenCodec(secret: 'super-secret-key'),
            clock     : new Clock()
        );

        $token = $jwt->issue(user: $activeUser);

        $this->assertNull(actual: $jwt->resolve(token: $token->token));
    }

    /**
     * @throws DateMalformedStringException
     */
    public function testJwtIdentityIssuesRefreshTokenWhenStoreConfigured() : void
    {
        $user = new User(
            id          : new UserId(value: 11),
            email       : new UserEmail(value: 'refresh@example.com'),
            username    : 'refresh',
            passwordHash: 'hash'
        );

        $store = new InMemoryRefreshTokenStore();
        $jwt   = new JwtIdentity(
            userSource       : new InMemoryUserSource(),
            codec            : new HmacTokenCodec(secret: 'super-secret-key'),
            clock            : new Clock(),
            refreshTokenStore: $store
        );

        $refresh = $jwt->issueRefreshToken(user: $user);

        $this->assertNotNull(actual: $refresh);
        $this->assertSame(expected: $user->getId()->value, actual: $refresh?->userId->value);
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function testJwtIdentityPreservesOAuthClientClaims() : void
    {
        $user = new User(
            id          : new UserId(value: 12),
            email       : new UserEmail(value: 'oauth-claims@example.com'),
            username    : 'oauth-claims',
            passwordHash: 'hash'
        );

        $userSource = new InMemoryUserSource();
        $userSource->create(user: $user);

        $jwt = new JwtIdentity(
            userSource: $userSource,
            codec     : new HmacTokenCodec(secret: 'super-secret-key'),
            clock     : new Clock()
        );

        $issued   = $jwt->issue(
            user    : $user,
            clientId: 'oauth_client',
            scopes  : ['email', 'profile']
        );
        $resolved = $jwt->resolve(token: $issued->token);

        $this->assertNotNull(actual: $resolved);
        $this->assertSame(expected: 'oauth_client', actual: $resolved?->clientId);
        $this->assertSame(expected: ['email', 'profile'], actual: $resolved?->scopes);
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function testJwtIdentityPreservesSenderConstraintClaims() : void
    {
        $user = new User(
            id          : new UserId(value: 13),
            email       : new UserEmail(value: 'oauth-binding@example.com'),
            username    : 'oauth-binding',
            passwordHash: 'hash'
        );

        $userSource = new InMemoryUserSource();
        $userSource->create(user: $user);

        $jwt = new JwtIdentity(
            userSource: $userSource,
            codec     : new HmacTokenCodec(secret: 'super-secret-key'),
            clock     : new Clock()
        );

        $issued   = $jwt->issue(
            user            : $user,
            clientId        : 'oauth_client',
            scopes          : ['profile'],
            senderConstraint: new OAuthSenderConstraint(
                                  type      : OAuthSenderConstraintType::DPOP,
                                  thumbprint: 'thumb-123'
                              )
        );
        $resolved = $jwt->resolve(token: $issued->token);

        $this->assertNotNull(actual: $resolved);
        $this->assertNotNull(actual: $resolved?->senderConstraint);
        $this->assertSame(expected: OAuthSenderConstraintType::DPOP, actual: $resolved?->senderConstraint?->type);
        $this->assertSame(expected: 'thumb-123', actual: $resolved?->senderConstraint?->thumbprint);
    }

    public function testJwtIdentityRejectsTokensWithUnexpectedIssuer() : void
    {
        $user = new User(
            id          : new UserId(value: 14),
            email       : new UserEmail(value: 'issuer-check@example.com'),
            username    : 'issuer-check',
            passwordHash: 'hash'
        );

        $userSource = new InMemoryUserSource();
        $userSource->create(user: $user);

        $jwt = new JwtIdentity(
            userSource: $userSource,
            codec     : new HmacTokenCodec(secret: 'super-secret-key'),
            clock     : new Clock()
        );

        $token = (new HmacTokenCodec(secret: 'super-secret-key'))->encode(claims: [
            'iss' => 'different-issuer',
            'sub' => $user->getId()->value,
            'iat' => 1,
            'nbf' => 1,
            'exp' => 2,
            'jti' => 'token-issuer-check',
        ]);

        $this->assertNull(actual: $jwt->resolve(token: $token));
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function testJwtIdentityIssuesAndResolvesWorkloadTokens() : void
    {
        $jwt = new JwtIdentity(
            userSource     : new InMemoryUserSource(),
            codec          : new HmacTokenCodec(secret: 'super-secret-key'),
            clock          : new Clock(),
            revocationStore: new InMemoryTokenRevocationStore()
        );

        $issued   = $jwt->issueWorkloadToken(
            subject         : 'client:machine-worker',
            clientId        : 'oauth_machine',
            scopes          : ['metrics.read', 'orders.sync'],
            senderConstraint: new OAuthSenderConstraint(
                                  type      : OAuthSenderConstraintType::MTLS,
                                  thumbprint: 'cert-thumb-1'
                              ),
            audience        : 'orders-api'
        );
        $resolved = $jwt->resolveWorkloadToken(
            token           : $issued->token,
            expectedAudience: 'orders-api'
        );

        $this->assertNotNull(actual: $resolved);
        $this->assertSame(expected: 'client:machine-worker', actual: $resolved?->subject);
        $this->assertSame(expected: 'oauth_machine', actual: $resolved?->clientId);
        $this->assertSame(expected: ['metrics.read', 'orders.sync'], actual: $resolved?->scopes);
        $this->assertSame(expected: 'orders-api', actual: $resolved?->audience);
        $this->assertSame(expected: OAuthSenderConstraintType::MTLS, actual: $resolved?->senderConstraint?->type);

        $this->assertNull(actual: $jwt->resolveWorkloadToken(token: $issued->token, expectedAudience: 'billing-api'));

        $jwt->revoke(tokenId: $issued->tokenId, expiresAt: $issued->expiresAt);

        $this->assertNull(actual: $jwt->resolveWorkloadToken(token: $issued->token, expectedAudience: 'orders-api'));
    }

    #[\Override]
    protected function tearDown() : void
    {
        Mockery::close();
    }
}

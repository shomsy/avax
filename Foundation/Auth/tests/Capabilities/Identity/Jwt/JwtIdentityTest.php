<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\Identity\Jwt;

use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraint;
use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraintType;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserEmail;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\Token\HmacTokenCodec;
use Avax\Auth\System\Flow\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Flow\Token\InMemoryTokenRevocationStore;
use Avax\Auth\System\Foundation\Clock;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for JWT identity state handling.
 */
class JwtIdentityTest extends TestCase
{
    public function testJwtIdentityIssueResolveAndRevokeCycle() : void
    {
        $user = new User(
            id          : new UserId(value: 7),
            email       : new UserEmail(value: 'jwt@example.com'),
            username    : 'jwt-user',
            passwordHash: 'hash'
        );

        $userSource = new InMemoryUserSource();
        $userSource->create($user);
        $revocationStore = new InMemoryTokenRevocationStore();

        $jwt = new JwtIdentity(
            userSource       : $userSource,
            codec            : new HmacTokenCodec(secret: 'super-secret-key'),
            clock            : new Clock(),
            revocationStore  : $revocationStore,
            refreshTokenStore: new InMemoryRefreshTokenStore()
        );

        $issued   = $jwt->issue(user: $user);
        $resolved = $jwt->resolve($issued->token);

        $this->assertNotNull($resolved);
        $this->assertSame($user->getId()->value, $resolved?->user->getId()->value);

        $jwt->revoke($issued->tokenId, $issued->expiresAt);

        $this->assertNull($jwt->resolve($issued->token));
    }

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
            ->with(Mockery::on(fn ($id) => $id instanceof UserId && $id->value === 9))
            ->andReturn($inactiveUser);

        $jwt = new JwtIdentity(
            userSource: $userSource,
            codec     : new HmacTokenCodec(secret: 'super-secret-key'),
            clock     : new Clock()
        );

        $token = $jwt->issue(user: $activeUser);

        $this->assertNull($jwt->resolve($token->token));
    }

    public function testJwtIdentityIssuesRefreshTokenWhenStoreConfigured() : void
    {
        $user = new User(
            id          : new UserId(11),
            email       : new UserEmail('refresh@example.com'),
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

        $refresh = $jwt->issueRefreshToken($user);

        $this->assertNotNull($refresh);
        $this->assertSame($user->getId()->value, $refresh?->userId->value);
    }

    public function testJwtIdentityPreservesOAuthClientClaims() : void
    {
        $user = new User(
            id          : new UserId(12),
            email       : new UserEmail('oauth-claims@example.com'),
            username    : 'oauth-claims',
            passwordHash: 'hash'
        );

        $userSource = new InMemoryUserSource();
        $userSource->create($user);

        $jwt = new JwtIdentity(
            userSource: $userSource,
            codec     : new HmacTokenCodec(secret: 'super-secret-key'),
            clock     : new Clock()
        );

        $issued   = $jwt->issue(
            user     : $user,
            clientId : 'oauth_client',
            scopes   : ['email', 'profile']
        );
        $resolved = $jwt->resolve($issued->token);

        $this->assertNotNull($resolved);
        $this->assertSame('oauth_client', $resolved?->clientId);
        $this->assertSame(['email', 'profile'], $resolved?->scopes);
    }

    public function testJwtIdentityPreservesSenderConstraintClaims() : void
    {
        $user = new User(
            id          : new UserId(13),
            email       : new UserEmail('oauth-binding@example.com'),
            username    : 'oauth-binding',
            passwordHash: 'hash'
        );

        $userSource = new InMemoryUserSource();
        $userSource->create($user);

        $jwt = new JwtIdentity(
            userSource: $userSource,
            codec     : new HmacTokenCodec(secret: 'super-secret-key'),
            clock     : new Clock()
        );

        $issued = $jwt->issue(
            user            : $user,
            clientId        : 'oauth_client',
            scopes          : ['profile'],
            senderConstraint: new OAuthSenderConstraint(
                type      : OAuthSenderConstraintType::DPOP,
                thumbprint: 'thumb-123'
            )
        );
        $resolved = $jwt->resolve($issued->token);

        $this->assertNotNull($resolved);
        $this->assertNotNull($resolved?->senderConstraint);
        $this->assertSame(OAuthSenderConstraintType::DPOP, $resolved?->senderConstraint?->type);
        $this->assertSame('thumb-123', $resolved?->senderConstraint?->thumbprint);
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }
}

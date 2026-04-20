<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\Token;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\InMemoryMfaStore;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\InMemoryRefreshTokenStore;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\RefreshAuthentication;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\RefreshAuthenticationFailed;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\RefreshAuthenticationRequest;
use Avax\Auth\System\Capabilities\Identity\User\User;
use Avax\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\InMemoryEmailVerificationStateStore;
use Avax\Auth\System\Foundation\Clock;
use DateTimeImmutable;
use Mockery;
use PHPUnit\Framework\TestCase;

final class RefreshAuthenticationTest extends TestCase
{
    public function testRefreshAuthenticationRejectsRotatedTokenReuseAndRevokesFamily() : void
    {
        $user = new User(
            id          : new UserId(value: 21),
            email       : new UserEmail(value: 'refresh-reuse@example.com'),
            username    : 'refresh-reuse',
            passwordHash: 'hash'
        );

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldNotReceive('findById');

        $projectAuthenticatedUser = new ProjectAuthenticatedUser(
            emailVerificationState: new InMemoryEmailVerificationStateStore(),
            mfaStore              : new InMemoryMfaStore()
        );
        $currentAuthentication    = new CurrentAuthentication();
        $auditLog                 = Mockery::mock(AuditLogInterface::class);
        $auditLog->shouldReceive('record')
            ->once()
            ->with(Mockery::on(static function (AuditEvent $event) : bool {
                return $event->name === 'auth.refresh.reuse_detected'
                    && $event->context['user_id'] === 21
                    && $event->context['family_id'] === 'family-21';
            }));

        $refreshTokens = new InMemoryRefreshTokenStore();
        $issued        = $refreshTokens->issue(
            userId    : new UserId(value: 21),
            expiresAt : new DateTimeImmutable(datetime: '+1 hour'),
            familyId  : 'family-21'
        );
        $refreshTokens->markRotated(tokenId: $issued->tokenId, replacementTokenId: 'replacement-token');
        $jwtIdentity = Mockery::mock(JwtIdentityInterface::class);
        $jwtIdentity->shouldNotReceive('issue');

        $refreshAuthentication = new RefreshAuthentication(
            userSource            : $userSource,
            projectAuthenticatedUser: $projectAuthenticatedUser,
            currentAuthentication : $currentAuthentication,
            auditLog              : $auditLog,
            clock                 : new Clock(),
            refreshTokenStore     : $refreshTokens,
            jwtIdentity           : $jwtIdentity
        );

        $this->expectException(RefreshAuthenticationFailed::class);
        $this->expectExceptionMessage('Refresh token is invalid.');

        $refreshAuthentication->execute(request: new RefreshAuthenticationRequest(refreshToken: $issued->token));
    }

    public function testRefreshAuthenticationRejectsUnknownRefreshToken() : void
    {
        $userSource = Mockery::mock(UserSourceInterface::class);
        $projectAuthenticatedUser = new ProjectAuthenticatedUser(
            emailVerificationState: new InMemoryEmailVerificationStateStore(),
            mfaStore              : new InMemoryMfaStore()
        );
        $currentAuthentication    = new CurrentAuthentication();
        $auditLog                 = Mockery::mock(AuditLogInterface::class);
        $auditLog->shouldReceive('record')->once();
        $refreshTokens            = new InMemoryRefreshTokenStore();
        $jwtIdentity              = Mockery::mock(JwtIdentityInterface::class);
        $jwtIdentity->shouldNotReceive('issue');

        $refreshAuthentication = new RefreshAuthentication(
            userSource              : $userSource,
            projectAuthenticatedUser: $projectAuthenticatedUser,
            currentAuthentication   : $currentAuthentication,
            auditLog                : $auditLog,
            clock                   : new Clock(),
            refreshTokenStore       : $refreshTokens,
            jwtIdentity             : $jwtIdentity
        );

        $this->expectException(RefreshAuthenticationFailed::class);
        $this->expectExceptionMessage('Refresh token is invalid.');

        $refreshAuthentication->execute(request: new RefreshAuthenticationRequest(refreshToken: 'unknown-token'));
    }
}

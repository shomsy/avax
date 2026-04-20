<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Auth\System\Capabilities\Identity\User\User;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Auth\System\Foundation\Clock;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Canonical ingress owner for request authentication.
 */
final readonly class AuthenticateRequest
{
    public function __construct(
        #[SensitiveParameter] private CurrentAuthentication $currentAuthentication,
        private ProjectAuthenticatedUser $projectAuthenticatedUser,
        private UserSourceInterface $userSource,
        private AuditLogInterface $auditLog,
        private Clock $clock,
        #[SensitiveParameter] private ?SessionIdentityInterface $sessionIdentity = null,
        #[SensitiveParameter] private ?JwtIdentityInterface $jwtIdentity = null
    ) {}

    public function execute(AuthenticationRequest $request) : AuthenticationContext
    {
        [$sessionUser, $sessionId, $sessionMfaVerifiedAt, $sessionPhishingResistant] = $this->resolveSessionUser(request: $request);
        $resolvedToken = $request->bearerToken !== null
            ? $this->jwtIdentity?->resolve(token: $request->bearerToken)
            : null;

        if (
            $sessionUser !== null
            && $resolvedToken !== null
            && $sessionUser->getId()->value !== $resolvedToken->user->getId()->value
        ) {
            $context = AuthenticationContext::guest(reason: 'credential_conflict');

            $this->auditLog->record(event: new AuditEvent(
                                               name      : 'auth.ingress.conflict',
                                               occurredAt: $this->clock->now(),
                                               context   : [
                                                               'ip_address' => $request->ipAddress,
                                                               'user_agent' => $request->userAgent,
                                                           ]
                                           ));

            $this->currentAuthentication->store(context: $context);

            return $context;
        }

        if ($sessionUser !== null && $resolvedToken !== null) {
            $context = AuthenticationContext::authenticated(
                user                : $this->projectAuthenticatedUser->fromUser(user: $sessionUser),
                mode                : AuthenticationMode::HYBRID,
                sessionId           : $sessionId,
                accessTokenId       : $resolvedToken->tokenId,
                accessTokenExpiresAt: $resolvedToken->expiresAt,
                refreshTokenFamilyId: $resolvedToken->familyId,
                mfaVerifiedAt       : $this->latestMfaMoment(left: $sessionMfaVerifiedAt, right: $resolvedToken->mfaVerifiedAt),
                phishingResistant   : $sessionPhishingResistant || $resolvedToken->phishingResistant
            );

            $this->currentAuthentication->store(context: $context);

            return $context;
        }

        if ($sessionUser !== null) {
            $context = AuthenticationContext::authenticated(
                user             : $this->projectAuthenticatedUser->fromUser(user: $sessionUser),
                mode             : AuthenticationMode::SESSION,
                sessionId        : $sessionId,
                mfaVerifiedAt    : $sessionMfaVerifiedAt,
                phishingResistant: $sessionPhishingResistant
            );

            $this->currentAuthentication->store(context: $context);

            return $context;
        }

        if ($resolvedToken !== null) {
            $context = AuthenticationContext::authenticated(
                user                : $this->projectAuthenticatedUser->fromUser(user: $resolvedToken->user),
                mode                : AuthenticationMode::TOKEN,
                accessTokenId       : $resolvedToken->tokenId,
                accessTokenExpiresAt: $resolvedToken->expiresAt,
                refreshTokenFamilyId: $resolvedToken->familyId,
                mfaVerifiedAt       : $resolvedToken->mfaVerifiedAt,
                phishingResistant   : $resolvedToken->phishingResistant
            );

            $this->currentAuthentication->store(context: $context);

            return $context;
        }

        $context = AuthenticationContext::guest(reason: 'unauthenticated');
        $this->currentAuthentication->store(context: $context);

        return $context;
    }

    /**
     * @return array{0: User|null, 1: string|null, 2: DateTimeImmutable|null, 3: bool}
     */
    private function resolveSessionUser(AuthenticationRequest $request) : array
    {
        if (! $request->allowSession || $this->sessionIdentity === null) {
            return [null, null, null, false];
        }

        $userId = $this->sessionIdentity->resolveUserId();

        if ($userId === null) {
            return [null, null, null, false];
        }

        $user = $this->userSource->findById(id: new UserId(value: $userId));

        if ($user === null || ! $user->isActive()) {
            return [null, null, null, false];
        }

        return [
            $user,
            $this->sessionIdentity->currentSessionId(),
            $this->sessionIdentity->resolveMfaVerifiedAt(),
            $this->sessionIdentity->resolvePhishingResistant(),
        ];
    }

    private function latestMfaMoment(
        DateTimeImmutable|null $left,
        DateTimeImmutable|null $right
    ) : DateTimeImmutable|null
    {
        if ($left === null) {
            return $right;
        }

        if ($right === null) {
            return $left;
        }

        return max($left, $right);
    }
}

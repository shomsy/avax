<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Record\ResolvedToken;
use DateTimeImmutable;
use SensitiveParameter;

/**
 * Canonical ingress owner for request authentication.
 */
final readonly class AuthenticateRequest
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication     $currentAuthentication,
        private ProjectAuthenticatedUser  $projectAuthenticatedUser,
        private UserSourceInterface       $userSource,
        private AuditLogInterface         $auditLog,
        private Clock                     $clock,
        #[SensitiveParameter]
        private ?SessionIdentityInterface $sessionIdentity = null,
        #[SensitiveParameter]
        private ?JwtIdentityInterface     $jwtIdentity = null,
    ) {}

    public function execute(AuthenticationRequest $authenticationRequest) : AuthenticationContext
    {
        [$sessionUser, $sessionId, $sessionMfaVerifiedAt, $sessionPhishingResistant] = $this->resolveSessionUser(request: $authenticationRequest);
        $resolvedToken = $authenticationRequest->bearerToken !== null
            ? $this->jwtIdentity?->resolve(token: $authenticationRequest->bearerToken)
            : null;

        if (
            $sessionUser !== null
            && $resolvedToken instanceof ResolvedToken
            && $sessionUser->getId()->value !== $resolvedToken->user->getId()->value
        ) {
            $context = AuthenticationContext::guest(reason: 'credential_conflict');

            $this->auditLog->record(event: new AuditEvent(
                                               name      : 'auth.ingress.conflict',
                                               occurredAt: $this->clock->now(),
                                               context   : [
                                                               'ip_address' => $authenticationRequest->ipAddress,
                                                               'user_agent' => $authenticationRequest->userAgent,
                                                           ],
                                           ));

            $this->currentAuthentication->store(context: $context);

            return $context;
        }

        if ($sessionUser !== null && $resolvedToken instanceof ResolvedToken) {
            $context = AuthenticationContext::authenticated(
                sessionId           : $sessionId,
                accessTokenId       : $resolvedToken->tokenId,
                accessTokenExpiresAt: $resolvedToken->expiresAt,
                refreshTokenFamilyId: $resolvedToken->familyId,
                mfaVerifiedAt       : $this->latestMfaMoment(left: $sessionMfaVerifiedAt, right: $resolvedToken->mfaVerifiedAt),
                phishingResistant   : $sessionPhishingResistant || $resolvedToken->phishingResistant,
                user                : $this->projectAuthenticatedUser->fromUser(user: $sessionUser),
                mode                : AuthenticationMode::HYBRID,
            );

            $this->currentAuthentication->store(context: $context);

            return $context;
        }

        if ($sessionUser !== null) {
            $context = AuthenticationContext::authenticated(
                sessionId        : $sessionId,
                mfaVerifiedAt    : $sessionMfaVerifiedAt,
                phishingResistant: $sessionPhishingResistant,
                user             : $this->projectAuthenticatedUser->fromUser(user: $sessionUser),
                mode             : AuthenticationMode::SESSION,
            );

            $this->currentAuthentication->store(context: $context);

            return $context;
        }

        if ($resolvedToken instanceof ResolvedToken) {
            $context = AuthenticationContext::authenticated(
                accessTokenId       : $resolvedToken->tokenId,
                accessTokenExpiresAt: $resolvedToken->expiresAt,
                refreshTokenFamilyId: $resolvedToken->familyId,
                mfaVerifiedAt       : $resolvedToken->mfaVerifiedAt,
                phishingResistant   : $resolvedToken->phishingResistant,
                user                : $this->projectAuthenticatedUser->fromUser(user: $resolvedToken->user),
                mode                : AuthenticationMode::TOKEN,
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
    private function resolveSessionUser(AuthenticationRequest $authenticationRequest) : array
    {
        if (! $authenticationRequest->allowSession || ! $this->sessionIdentity instanceof SessionIdentityInterface) {
            return [null, null, null, false];
        }

        $userId = $this->sessionIdentity->resolveUserId();

        if ($userId === null) {
            return [null, null, null, false];
        }

        $user = $this->userSource->findById(id: new UserId(value: $userId));

        if (! $user instanceof User || ! $user->isActive()) {
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
        ?DateTimeImmutable $left,
        ?DateTimeImmutable $right,
    ) : ?DateTimeImmutable
    {
        if (! $left instanceof DateTimeImmutable) {
            return $right;
        }

        if (! $right instanceof DateTimeImmutable) {
            return $left;
        }

        return max($left, $right);
    }
}

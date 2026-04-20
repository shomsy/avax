<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Tokens\Runtime;

use Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Support\DeterministicRiskEngine;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Auth\System\Foundation\Clock;
use DateMalformedStringException;
use SensitiveParameter;

/**
 * Rotates refresh tokens and re-issues access state.
 */
final readonly class RefreshAuthentication
{
    private DeterministicRiskEngine|null    $riskEngine;
    private JwtIdentityInterface|null       $jwtIdentity;
    private RefreshTokenStoreInterface|null $refreshTokenStore;
    private Clock                           $clock;
    private AuditLogInterface               $auditLog;
    private CurrentAuthentication           $currentAuthentication;
    private ProjectAuthenticatedUser        $projectAuthenticatedUser;
    private UserSourceInterface             $userSource;

    public function __construct(
        UserSourceInterface                                   $userSource,
        ProjectAuthenticatedUser                              $projectAuthenticatedUser,
        #[SensitiveParameter] CurrentAuthentication           $currentAuthentication,
        AuditLogInterface                                     $auditLog,
        Clock                                                 $clock,
        #[SensitiveParameter] RefreshTokenStoreInterface|null $refreshTokenStore = null,
        #[SensitiveParameter] JwtIdentityInterface|null       $jwtIdentity = null,
        DeterministicRiskEngine|null                          $riskEngine = null
    )
    {
        $this->userSource               = $userSource;
        $this->projectAuthenticatedUser = $projectAuthenticatedUser;
        $this->currentAuthentication    = $currentAuthentication;
        $this->auditLog                 = $auditLog;
        $this->clock                    = $clock;
        $this->refreshTokenStore        = $refreshTokenStore;
        $this->jwtIdentity              = $jwtIdentity;
        $this->riskEngine               = $riskEngine;
    }

    /**
     * @throws RefreshAuthenticationFailed
     * @throws DateMalformedStringException
     */
    public function execute(RefreshAuthenticationRequest $request) : AuthenticationResult
    {
        if ($this->refreshTokenStore === null || $this->jwtIdentity === null) {
            throw RefreshAuthenticationFailed::invalidToken();
        }

        $record = $this->refreshTokenStore->find(plainToken: $request->refreshToken);
        $now    = $this->clock->now();

        if ($record === null || $record->revoked || $record->isExpiredAt(moment: $now)) {
            $this->auditLog->record(event: new AuditEvent(
                                               name      : 'auth.refresh.failed',
                                               occurredAt: $now,
                                               context   : [
                                                               'reason'     => 'missing_or_expired',
                                                               'ip_address' => $request->ipAddress,
                                                               'user_agent' => $request->userAgent,
                                                           ]
                                           ));

            throw RefreshAuthenticationFailed::invalidToken();
        }

        if ($record->clientId !== null) {
            $this->auditLog->record(event: new AuditEvent(
                                               name      : 'auth.refresh.failed',
                                               occurredAt: $now,
                                               context   : [
                                                               'reason'     => 'oauth_bound_token',
                                                               'ip_address' => $request->ipAddress,
                                                               'user_agent' => $request->userAgent,
                                                           ]
                                           ));

            throw RefreshAuthenticationFailed::invalidToken();
        }

        if ($record->wasRotated()) {
            $this->refreshTokenStore->revokeFamily(familyId: $record->familyId);
            $riskDecision = $this->riskEngine?->recordRefreshReuse(userId: $record->userId->value, clientId: $record->clientId);
            $this->auditLog->record(event: new AuditEvent(
                                               name      : 'auth.refresh.reuse_detected',
                                               occurredAt: $now,
                                               context   : [
                                                               'user_id'     => $record->userId->value,
                                                               'family_id'   => $record->familyId,
                                                               'risk_action' => $riskDecision?->action->value,
                                                               'ip_address'  => $request->ipAddress,
                                                               'user_agent'  => $request->userAgent,
                                                           ]
                                           ));

            throw RefreshAuthenticationFailed::invalidToken();
        }

        $user = $this->userSource->findById(id: $record->userId);

        if ($user === null || ! $user->isActive()) {
            $this->refreshTokenStore->revokeFamily(familyId: $record->familyId);
            throw RefreshAuthenticationFailed::invalidToken();
        }

        $accessToken  = $this->jwtIdentity->issue(
            user                : $user,
            mfaVerifiedAt       : $record->mfaVerifiedAt,
            phishingResistant   : $record->phishingResistant,
            clientId            : $record->clientId,
            scopes              : $record->scopes,
            refreshTokenFamilyId: $record->familyId
        );
        $refreshToken = $this->refreshTokenStore->issue(
            userId           : $record->userId,
            expiresAt        : $now->modify(modifier: '+30 days'),
            familyId         : $record->familyId,
            mfaVerifiedAt    : $record->mfaVerifiedAt,
            phishingResistant: $record->phishingResistant,
            clientId         : $record->clientId,
            scopes           : $record->scopes
        );
        $this->refreshTokenStore->markRotated(tokenId: $record->tokenId, replacementTokenId: $refreshToken->tokenId);

        $context = AuthenticationContext::authenticated(
            user                : $this->projectAuthenticatedUser->fromUser(user: $user),
            mode                : AuthenticationMode::TOKEN,
            accessTokenId       : $accessToken->tokenId,
            accessTokenExpiresAt: $accessToken->expiresAt,
            refreshTokenId      : $refreshToken->tokenId,
            mfaVerifiedAt       : $record->mfaVerifiedAt,
            phishingResistant   : $record->phishingResistant
        );

        $this->currentAuthentication->store(context: $context);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.refresh.succeeded',
                                           occurredAt: $now,
                                           context   : [
                                                           'user_id'    => $user->getId()->value,
                                                           'family_id'  => $refreshToken->familyId,
                                                           'ip_address' => $request->ipAddress,
                                                           'user_agent' => $request->userAgent,
                                                       ]
                                       ));

        return AuthenticationResult::success(
            context     : $context,
            accessToken : $accessToken->token,
            refreshToken: $refreshToken->token
        );
    }
}

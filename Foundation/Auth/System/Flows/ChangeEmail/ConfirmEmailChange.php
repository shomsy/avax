<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\ChangeEmail;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Challenge\MfaChallengeStoreInterface;
use Avax\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryInterface;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\RefreshTokenStoreInterface;
use Avax\Auth\System\Capabilities\Identity\UserSource\ProvisionableUserSourceInterface;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\EmailVerificationStateStoreInterface;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class ConfirmEmailChange
{
    private RefreshTokenStoreInterface|null      $refreshTokenStore;
    private MfaChallengeStoreInterface|null      $mfaChallengeStore;
    private SessionRegistryInterface|null        $sessionRegistry;
    private IdentityInterface                    $identity;
    private CurrentAuthentication                $currentAuthentication;
    private Clock                                $clock;
    private AuditLogInterface                    $auditLog;
    private EmailVerificationStateStoreInterface $emailVerificationState;
    private EmailChangeStoreInterface            $emailChangeStore;
    private ProvisionableUserSourceInterface     $userSource;

    public function __construct(
        ProvisionableUserSourceInterface                           $userSource,
        #[SensitiveParameter] EmailChangeStoreInterface            $emailChangeStore,
        #[SensitiveParameter] EmailVerificationStateStoreInterface $emailVerificationState,
        AuditLogInterface                                          $auditLog,
        Clock                                                      $clock,
        #[SensitiveParameter] CurrentAuthentication                $currentAuthentication,
        IdentityInterface                                          $identity,
        #[SensitiveParameter] SessionRegistryInterface|null        $sessionRegistry = null,
        MfaChallengeStoreInterface|null                            $mfaChallengeStore = null,
        #[SensitiveParameter] RefreshTokenStoreInterface|null      $refreshTokenStore = null
    )
    {
        $this->userSource             = $userSource;
        $this->emailChangeStore       = $emailChangeStore;
        $this->emailVerificationState = $emailVerificationState;
        $this->auditLog               = $auditLog;
        $this->clock                  = $clock;
        $this->currentAuthentication  = $currentAuthentication;
        $this->identity               = $identity;
        $this->sessionRegistry        = $sessionRegistry;
        $this->mfaChallengeStore      = $mfaChallengeStore;
        $this->refreshTokenStore      = $refreshTokenStore;
    }

    /**
     * @throws EmailChangeFailed
     */
    public function execute(ConfirmEmailChangeData $data) : bool
    {
        $context = $this->currentAuthentication->read();
        $record  = $this->emailChangeStore->consume(token: $data->token, now: $this->clock->now());

        if ($record === null) {
            $this->auditLog->record(event: new AuditEvent(
                                               name      : 'auth.email_change.failed',
                                               occurredAt: $this->clock->now(),
                                               context   : [
                                                               'reason'     => 'invalid_token',
                                                               'ip_address' => $data->ipAddress,
                                                               'user_agent' => $data->userAgent,
                                                           ]
                                           ));

            throw EmailChangeFailed::invalidToken();
        }

        $existingUser = $this->userSource->findByEmail(email: $record->newEmail);

        if ($existingUser !== null && ! $existingUser->getId()->equals(other: $record->userId)) {
            throw EmailChangeFailed::emailInUse();
        }

        $this->userSource->updateEmail(id: $record->userId, email: $record->newEmail);
        $this->emailVerificationState->markVerified(userId: $record->userId);
        $this->sessionRegistry?->revokeForUser(userId: $record->userId, revokedAt: $this->clock->now(), reason: 'email_change');
        $this->mfaChallengeStore?->forgetForUser(userId: $record->userId);
        $this->refreshTokenStore?->revokeUser(userId: $record->userId);

        $currentUser = $context->user();

        if ($currentUser !== null && $currentUser->id === $record->userId->value) {
            $this->identity->clear(context: $context);
            $this->currentAuthentication->clear();
        }

        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.email_change.completed',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'user_id'    => $record->userId->value,
                                                           'new_email'  => $record->newEmail,
                                                           'ip_address' => $data->ipAddress,
                                                           'user_agent' => $data->userAgent,
                                                       ]
                                       ));

        return true;
    }
}

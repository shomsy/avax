<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\ChangeEmail;

use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\UserSource\ProvisionableUserSourceInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Mfa\Challenge\MfaChallengeStoreInterface;
use Avax\Auth\System\Flow\Token\RefreshTokenStoreInterface;
use Avax\Auth\System\Flow\Verify\EmailVerificationStateStoreInterface;
use Avax\Auth\System\Capability\Session\SessionRegistryInterface;
use Avax\Auth\System\Foundation\Clock;

final readonly class ConfirmEmailChange
{
    public function __construct(
        private ProvisionableUserSourceInterface $userSource,
        private EmailChangeStoreInterface $emailChangeStore,
        private EmailVerificationStateStoreInterface $emailVerificationState,
        private AuditLogInterface $auditLog,
        private Clock $clock,
        private CurrentAuthentication $currentAuthentication,
        private IdentityInterface $identity,
        private SessionRegistryInterface|null $sessionRegistry = null,
        private MfaChallengeStoreInterface|null $mfaChallengeStore = null,
        private RefreshTokenStoreInterface|null $refreshTokenStore = null
    ) {}

    /**
     * @throws EmailChangeFailed
     */
    public function execute(ConfirmEmailChangeData $data) : bool
    {
        $context = $this->currentAuthentication->read();
        $record = $this->emailChangeStore->consume($data->token, $this->clock->now());

        if ($record === null) {
            $this->auditLog->record(new AuditEvent(
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

        $existingUser = $this->userSource->findByEmail($record->newEmail);

        if ($existingUser !== null && ! $existingUser->getId()->equals($record->userId)) {
            throw EmailChangeFailed::emailInUse();
        }

        $this->userSource->updateEmail($record->userId, $record->newEmail);
        $this->emailVerificationState->markVerified($record->userId);
        $this->sessionRegistry?->revokeForUser($record->userId, $this->clock->now(), 'email_change');
        $this->mfaChallengeStore?->forgetForUser($record->userId);
        $this->refreshTokenStore?->revokeUser($record->userId);

        $currentUser = $context->user();

        if ($currentUser !== null && $currentUser->id === $record->userId->value) {
            $this->identity->clear($context);
            $this->currentAuthentication->clear();
        }

        $this->auditLog->record(new AuditEvent(
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

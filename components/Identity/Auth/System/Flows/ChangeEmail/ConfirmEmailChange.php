<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\ChangeEmail;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\MfaChallengeStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryInterface;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\ProvisionableUserSourceInterface;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification\EmailVerificationStateStoreInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class ConfirmEmailChange
{
    public function __construct(
        private ProvisionableUserSourceInterface     $userSource,
        #[SensitiveParameter]
        private EmailChangeStoreInterface            $emailChangeStore,
        #[SensitiveParameter]
        private EmailVerificationStateStoreInterface $emailVerificationState,
        private AuditLogInterface                    $auditLog,
        private Clock                                $clock,
        #[SensitiveParameter]
        private CurrentAuthentication                $currentAuthentication,
        private IdentityInterface                    $identity,
        #[SensitiveParameter]
        private SessionRegistryInterface|null        $sessionRegistry = null,
        private MfaChallengeStoreInterface|null      $mfaChallengeStore = null,
        #[SensitiveParameter]
        private RefreshTokenStoreInterface|null      $refreshTokenStore = null,
    ) {}

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
                                                           ],
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
                                                       ],
                                       ));

        return true;
    }
}

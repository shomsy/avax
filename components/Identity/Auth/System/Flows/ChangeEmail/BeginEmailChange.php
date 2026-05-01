<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\ChangeEmail;

use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\StepUp\RequireFreshMfa;
use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;
use DateMalformedStringException;
use SensitiveParameter;

final readonly class BeginEmailChange
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication $currentAuthentication,
        private UserSourceInterface $userSource,
        #[SensitiveParameter]
        private PasswordHasher $passwordHasher,
        #[SensitiveParameter]
        private EmailChangeStoreInterface $emailChangeStore,
        private RequireFreshMfa $requireFreshMfa,
        private AuditLogInterface $auditLog,
        private Clock $clock,
        private int $expiresAfterSeconds = 1800,
    ) {}

    /**
     * @throws EmailChangeFailed
     * @throws DateMalformedStringException
     * @throws Unauthenticated
     */
    public function execute(BeginEmailChangeData $data): EmailChangeChallenge
    {
        $context = $this->currentAuthentication->read();
        $actor = $context->user();

        if ($actor === null) {
            throw EmailChangeFailed::unauthenticated();
        }

        $user = $this->userSource->findById(id: new UserId(value: $actor->id));

        if ($user === null || ! $user->isActive()) {
            throw EmailChangeFailed::unauthenticated();
        }

        $newEmail = strtolower(string: trim(string: $data->newEmail));

        if ($newEmail === '' || $newEmail === strtolower(string: $user->getEmail()->value)) {
            throw EmailChangeFailed::invalidEmail();
        }

        if (! $this->passwordHasher->verify(password: $data->currentPassword, hash: $user->getPasswordHash())) {
            $this->auditLog->record(event: new AuditEvent(
                name      : 'auth.email_change.failed',
                occurredAt: $this->clock->now(),
                context   : [
                    'user_id' => $user->getId()->value,
                    'reason' => 'invalid_password',
                    'ip_address' => $data->ipAddress,
                    'user_agent' => $data->userAgent,
                ],
            ));

            throw EmailChangeFailed::invalidPassword();
        }

        if ($this->userSource->emailExists(email: $newEmail)) {
            throw EmailChangeFailed::emailInUse();
        }

        $this->requireFreshMfa->execute();
        $challenge = $this->emailChangeStore->issue(
            userId   : $user->getId(),
            newEmail : $newEmail,
            expiresAt: $this->clock->now()->modify(modifier: "+$this->expiresAfterSeconds seconds"),
        );

        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.email_change.requested',
            occurredAt: $this->clock->now(),
            context   : [
                'user_id' => $user->getId()->value,
                'new_email' => $newEmail,
                'ip_address' => $data->ipAddress,
                'user_agent' => $data->userAgent,
            ],
        ));

        return $challenge;
    }
}

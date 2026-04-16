<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\ChangeEmail;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Mfa\StepUp\RequireFreshMfa;
use Avax\Auth\System\Foundation\Clock;
use DateMalformedStringException;
use SensitiveParameter;

final readonly class BeginEmailChange
{
    private int                       $expiresAfterSeconds;
    private Clock                     $clock;
    private AuditLogInterface         $auditLog;
    private RequireFreshMfa           $requireFreshMfa;
    private EmailChangeStoreInterface $emailChangeStore;
    private PasswordHasher            $passwordHasher;
    private UserSourceInterface       $userSource;
    private CurrentAuthentication     $currentAuthentication;

    public function __construct(
        #[SensitiveParameter] CurrentAuthentication     $currentAuthentication,
        UserSourceInterface                             $userSource,
        #[SensitiveParameter] PasswordHasher            $passwordHasher,
        #[SensitiveParameter] EmailChangeStoreInterface $emailChangeStore,
        RequireFreshMfa                                 $requireFreshMfa,
        AuditLogInterface                               $auditLog,
        Clock                                           $clock,
        int                                             $expiresAfterSeconds = 1800
    )
    {
        $this->currentAuthentication = $currentAuthentication;
        $this->userSource            = $userSource;
        $this->passwordHasher        = $passwordHasher;
        $this->emailChangeStore      = $emailChangeStore;
        $this->requireFreshMfa       = $requireFreshMfa;
        $this->auditLog              = $auditLog;
        $this->clock                 = $clock;
        $this->expiresAfterSeconds   = $expiresAfterSeconds;
    }

    /**
     * @throws EmailChangeFailed
     * @throws DateMalformedStringException
     * @throws Unauthenticated
     */
    public function execute(BeginEmailChangeData $data) : EmailChangeChallenge
    {
        $context = $this->currentAuthentication->read();
        $actor   = $context->user();

        if ($actor === null) {
            throw EmailChangeFailed::unauthenticated();
        }

        $user = $this->userSource->findById(id: new UserId(value: $actor->id));

        if ($user === null || ! $user->isActive()) {
            throw EmailChangeFailed::unauthenticated();
        }

        $newEmail = strtolower(trim($data->newEmail));

        if ($newEmail === '' || $newEmail === strtolower($user->getEmail()->value)) {
            throw EmailChangeFailed::invalidEmail();
        }

        if (! $this->passwordHasher->verify(password: $data->currentPassword, hash: $user->getPasswordHash())) {
            $this->auditLog->record(event: new AuditEvent(
                                               name      : 'auth.email_change.failed',
                                               occurredAt: $this->clock->now(),
                                               context   : [
                                                               'user_id'    => $user->getId()->value,
                                                               'reason'     => 'invalid_password',
                                                               'ip_address' => $data->ipAddress,
                                                               'user_agent' => $data->userAgent,
                                                           ]
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
            expiresAt: $this->clock->now()->modify(modifier: "+{$this->expiresAfterSeconds} seconds")
        );

        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.email_change.requested',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'user_id'    => $user->getId()->value,
                                                           'new_email'  => $newEmail,
                                                           'ip_address' => $data->ipAddress,
                                                           'user_agent' => $data->userAgent,
                                                       ]
                                       ));

        return $challenge;
    }
}

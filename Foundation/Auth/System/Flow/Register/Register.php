<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Register;

use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserEmail;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Login\RateLimit\LoginRateLimit;
use Avax\Auth\System\Foundation\IdGeneratorInterface;
use SensitiveParameter;

/**
 * High-level orchestrator for user registration.
 *
 * Banal: The main Register file.
 */
final readonly class Register
{
    public function __construct(
        private UserSourceInterface                  $userSource,
        #[SensitiveParameter] private PasswordHasher $passwordHasher,
        private IdGeneratorInterface                 $idGenerator,
        private ProjectAuthenticatedUser             $projectAuthenticatedUser,
        private AuditLogInterface                    $auditLog,
        private bool                                 $emailVerificationRequired = false,
        private LoginRateLimit|null                  $rateLimit = null
    ) {}

    /**
     * @throws RegistrationFailed
     */
    public function execute(RegistrationData $data) : RegistrationResult
    {
        $this->rateLimit?->check(identifier: $data->email);

        if ($this->userSource->emailExists(email: $data->email)) {
            $this->rateLimit?->recordFailed(identifier: $data->email);

            throw RegistrationFailed::emailTaken();
        }

        if ($this->userSource->usernameExists(username: $data->username)) {
            $this->rateLimit?->recordFailed(identifier: $data->email);

            throw RegistrationFailed::usernameTaken();
        }

        $passwordHash = $this->passwordHasher->hash(password: $data->password);

        $user = User::create(
            id          : new UserId(value: $this->idGenerator->generate()),
            email       : new UserEmail(value: $data->email),
            username    : $data->username,
            passwordHash: $passwordHash
        );

        $createdUser = $this->userSource->create(user: $user);

        $this->rateLimit?->reset(identifier: $data->email);
        $this->auditLog->record(new AuditEvent(
                                    name      : 'auth.register.succeeded',
                                    occurredAt: new \DateTimeImmutable(),
                                    context   : [
                                                    'user_id'    => $createdUser->getId()->value,
                                                    'email'      => strtolower($createdUser->getEmail()->value),
                                                    'ip_address' => $data->ipAddress,
                                                    'user_agent' => $data->userAgent,
                                                ]
                                ));

        return new RegistrationResult(
            user                     : $this->projectAuthenticatedUser->fromUser($createdUser),
            emailVerificationRequired: $this->emailVerificationRequired
        );
    }
}

<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\Register;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\PasswordHashing\PasswordHasher;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\LoginRateLimit;
use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\RateLimitException;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Auth\System\Foundation\IdGeneratorInterface;
use SensitiveParameter;

/**
 * High-level orchestrator for user registration.
 */
final readonly class Register
{
    public function __construct(
        private UserSourceInterface                  $userSource,
        #[SensitiveParameter] private PasswordHasher $passwordHasher,
        private IdGeneratorInterface                 $idGenerator,
        private ProjectAuthenticatedUser             $projectAuthenticatedUser,
        private AuditLogInterface                    $auditLog,
        private Clock                                $clock,
        private bool                                 $emailVerificationRequired = false,
        private LoginRateLimit|null                  $rateLimit = null
    ) {}

    /**
     * @throws RegistrationFailed
     * @throws RateLimitException
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
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.register.succeeded',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'user_id'    => $createdUser->getId()->value,
                                                           'email'      => strtolower(string: $createdUser->getEmail()->value),
                                                           'ip_address' => $data->ipAddress,
                                                           'user_agent' => $data->userAgent,
                                                       ]
                                       ));

        return new RegistrationResult(
            user                     : $this->projectAuthenticatedUser->fromUser(user: $createdUser),
            emailVerificationRequired: $this->emailVerificationRequired
        );
    }
}

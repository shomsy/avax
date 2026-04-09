<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Register;

use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\User\UserEmail;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Flow\Login\RateLimit\LoginRateLimit;
use Avax\Auth\System\Foundation\IdGeneratorInterface;
use Exception;
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
        private LoginRateLimit|null                  $rateLimit = null
    ) {}

    /**
     * @throws Exception
     */
    public function execute(RegistrationData $data) : User
    {
        $this->rateLimit?->check(identifier: $data->email);

        if ($this->userSource->emailExists(email: $data->email)) {
            $this->rateLimit?->recordFailed(identifier: $data->email);

            throw new Exception(message: 'Email is already taken.', code: 409);
        }

        if ($this->userSource->usernameExists(username: $data->username)) {
            $this->rateLimit?->recordFailed(identifier: $data->email);

            throw new Exception(message: 'Username is already taken.', code: 409);
        }

        $passwordHash = $this->passwordHasher->hash(password: $data->password);
        
        $user = User::create(
            id: new UserId(value: $this->idGenerator->generate()),
            email: new UserEmail(value: $data->email),
            username: $data->username,
            passwordHash: $passwordHash
        );

        $createdUser = $this->userSource->create(user: $user);

        $this->rateLimit?->reset(identifier: $data->email);

        return $createdUser;
    }
}

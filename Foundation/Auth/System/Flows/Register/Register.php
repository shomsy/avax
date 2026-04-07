<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Register;

use Avax\Auth\System\Capabilities\User\User;
use Avax\Auth\System\Capabilities\User\UserId;
use Avax\Auth\System\Capabilities\User\UserEmail;
use Avax\Auth\System\Capabilities\UserSource\UserSourceInterface;
use Avax\Auth\System\Capabilities\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Flows\Login\RateLimit\LoginRateLimit;
use Avax\Auth\System\Foundation\IdGeneratorInterface;

/**
 * High-level orchestrator for user registration.
 * 
 * Banal: The main Register file.
 */
final readonly class Register
{
    public function __construct(
        private UserSourceInterface                   $userSource,
        #[\SensitiveParameter] private PasswordHasher $passwordHasher,
        private IdGeneratorInterface                  $idGenerator,
        private LoginRateLimit|null                   $rateLimit = null
    ) {}

    /**
     * @throws \Exception
     */
    public function execute(RegistrationData $data) : User
    {
        if ($this->rateLimit !== null) {
            $this->rateLimit->check(identifier: $data->email);
        }

        if ($this->userSource->emailExists(email: $data->email)) {
            if ($this->rateLimit !== null) {
                $this->rateLimit->recordFailed(identifier: $data->email);
            }

            throw new \Exception(message: 'Email is already taken.', code: 409);
        }

        if ($this->userSource->usernameExists(username: $data->username)) {
            if ($this->rateLimit !== null) {
                $this->rateLimit->recordFailed(identifier: $data->email);
            }

            throw new \Exception(message: 'Username is already taken.', code: 409);
        }

        $passwordHash = $this->passwordHasher->hash(password: $data->password);
        
        $user = User::create(
            id: new UserId($this->idGenerator->generate()),
            email: new UserEmail(value: $data->email),
            username: $data->username,
            passwordHash: $passwordHash
        );

        $createdUser = $this->userSource->create(user: $user);

        if ($this->rateLimit !== null) {
            $this->rateLimit->reset(identifier: $data->email);
        }

        return $createdUser;
    }
}

<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\Register;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Foundation\IdGeneratorInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;

/**
 * CreateRegisteredUser - Action to persist a new user entity.
 * 1:1 alignment with refactor.md.
 */
final readonly class CreateRegisteredUser
{
    public function __construct(
        private UserSourceInterface $userSource,
        private IdGeneratorInterface $idGenerator,
    ) {}

    public function execute(RegistrationData $registrationData, string $hashedPassword) : User
    {
        $user = new User(
            id           : new UserId($this->idGenerator->generate()),
            email        : new UserEmail($registrationData->email),
            username     : $registrationData->username ?? $registrationData->email,
            passwordHash : $hashedPassword,
        );

        return $this->userSource->create($user);
    }
}

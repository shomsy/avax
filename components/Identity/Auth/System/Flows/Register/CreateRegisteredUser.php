<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\Register;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSource;

/**
 * CreateRegisteredUser - Action to persist a new user entity.
 * 1:1 alignment with refactor.md.
 */
final readonly class CreateRegisteredUser
{
    public function __construct(
        private UserSource $userSource,
    ) {}

    public function execute(RegistrationData $registrationData, string $hashedPassword) : User
    {
        return $this->userSource->create([
                                             'email'    => $registrationData->email,
                                             'password' => $hashedPassword,
                                             'username' => $registrationData->username ?? $registrationData->email,
                                         ]);
    }
}

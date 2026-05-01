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
        private UserSource $source,
    ) {}

    public function execute(RegistrationData $data, string $hashedPassword): User
    {
        return $this->source->create([
            'email' => $data->email,
            'password' => $hashedPassword,
            'username' => $data->username ?? $data->email,
        ]);
    }
}

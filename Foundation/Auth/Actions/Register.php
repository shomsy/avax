<?php

declare(strict_types=1);

namespace Avax\Auth\Actions;

use Avax\Auth\Contracts\UserSourceInterface;
use Avax\Auth\Contracts\UserInterface;
use Avax\Auth\Data\RegistrationData;

final readonly class Register
{
    public function __construct(private UserSourceInterface $userProvider) {}

    public function execute(RegistrationData $data): UserInterface
    {
        return $this->userProvider->createUser($data);
    }
}

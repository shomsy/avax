<?php

declare(strict_types=1);

namespace Avax\Auth\Actions;

use Avax\Auth\Contracts\UserInterface;
use Avax\Auth\Contracts\UserSourceInterface;
use Avax\Auth\Data\RegistrationDTO;

final readonly class Register
{
    public function __construct(private UserSourceInterface $userProvider) {}

    public function execute(RegistrationDTO $data) : UserInterface
    {
        return $this->userProvider->createUser(RegistrationDTO: $data);
    }
}

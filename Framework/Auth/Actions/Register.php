<?php

declare(strict_types=1);

namespace Gemini\Auth\Actions;

use Gemini\Auth\Contracts\UserSourceInterface;
use Gemini\Auth\Contracts\UserInterface;
use Gemini\Auth\Data\RegistrationData;

final readonly class Register
{
    public function __construct(private UserSourceInterface $userProvider) {}

    public function execute(RegistrationData $data): UserInterface
    {
        return $this->userProvider->createUser($data);
    }
}

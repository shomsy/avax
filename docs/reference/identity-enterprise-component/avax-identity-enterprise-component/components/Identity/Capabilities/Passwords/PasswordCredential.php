<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Passwords;

use Avax\Components\Identity\Foundation\Values\PasswordHash;
use Avax\Components\Identity\Foundation\Values\UserId;

final readonly class PasswordCredential
{
    public function __construct(private UserId $userId, private PasswordHash $passwordHash) {}

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function passwordHash(): PasswordHash
    {
        return $this->passwordHash;
    }
}

<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\PublicSurface;

use Avax\Components\Auth\System\Capabilities\Identity\User;

final class Auth implements AuthInterface
{
    private User|null $currentUser = null;

    public function user(): User|null
    {
        return $this->currentUser;
    }

    public function check(): bool
    {
        return $this->currentUser !== null;
    }

    public function guest(): bool
    {
        return $this->currentUser === null;
    }

    public function setUser(User $user): void
    {
        $this->currentUser = $user;
    }
}
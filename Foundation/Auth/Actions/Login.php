<?php

declare(strict_types=1);

namespace Avax\Auth\Actions;

use Avax\Auth\Contracts\IdentityInterface;
use Avax\Auth\Contracts\UserInterface;
use Avax\Auth\Data\Credentials;
use Avax\Auth\Exceptions\AuthFailed;

final readonly class Login
{
    public function __construct(private IdentityInterface $identity) {}

    /**
     * @throws AuthFailed
     */
    public function execute(Credentials $credentials): UserInterface
    {
        if (! $this->identity->attempt(credentials: $credentials)) {
            throw new AuthFailed(message: 'Invalid credentials provided.');
        }

        $user = $this->identity->user();

        if ($user === null) {
            // Should theoretically not happen if attempt returned true, but safe guard.
            throw new AuthFailed(message: 'Authentication check passed but user retrieval failed.');
        }

        return $user;
    }
}

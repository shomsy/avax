<?php

declare(strict_types=1);

namespace Gemini\Auth\Actions;

use Gemini\Auth\Contracts\IdentityInterface;
use Gemini\Auth\Contracts\UserInterface;
use Gemini\Auth\Data\Credentials;
use Gemini\Auth\Exceptions\AuthFailed;

final readonly class Login
{
    public function __construct(private IdentityInterface $identity) {}

    public function execute(Credentials $credentials): UserInterface
    {
        if (! $this->identity->attempt($credentials)) {
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

<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Register;

use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;

/**
 * Stable public result of a registration flow.
 */
final readonly class RegistrationResult
{
    public function __construct(
        private AuthenticatedUser $user,
        private bool              $emailVerificationRequired = false
    ) {}

    public function user() : AuthenticatedUser
    {
        return $this->user;
    }

    public function emailVerificationRequired() : bool
    {
        return $this->emailVerificationRequired;
    }
}

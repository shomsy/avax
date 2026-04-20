<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Register;

use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;

/**
 * Stable public result of a registration flow.
 */
final readonly class RegistrationResult
{
    private bool              $emailVerificationRequired;
    private AuthenticatedUser $user;

    public function __construct(
        AuthenticatedUser $user,
        bool              $emailVerificationRequired = false
    )
    {
        $this->user                      = $user;
        $this->emailVerificationRequired = $emailVerificationRequired;
    }

    public function user() : AuthenticatedUser
    {
        return $this->user;
    }

    public function emailVerificationRequired() : bool
    {
        return $this->emailVerificationRequired;
    }
}

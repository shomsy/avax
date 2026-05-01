<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\Register;

use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;

/**
 * Stable public result of a registration flow.
 */
final readonly class RegistrationResult
{
    public function __construct(private AuthenticatedUser $authenticatedUser, private bool $emailVerificationRequired = false) {}

    public function user(): AuthenticatedUser
    {
        return $this->authenticatedUser;
    }

    public function emailVerificationRequired(): bool
    {
        return $this->emailVerificationRequired;
    }
}

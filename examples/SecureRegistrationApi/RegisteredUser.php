<?php

declare(strict_types=1);

namespace Avax\Examples\SecureRegistrationApi;

/**
 * RegisteredUser — Read model for the registered user projection.
 *
 * This is the CQRS read-side representation.
 * Built by ProjectRegisteredUser from UserRegistered events.
 *
 * Kept inside the reference flow/capability — no generic CQRS folder.
 */
final readonly class RegisteredUser
{
    public function __construct(
        public string $userId,
        public string $email,
        public string $registeredAt,
    ) {
    }
}

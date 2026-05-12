<?php

declare(strict_types=1);

namespace Avax\Examples\SecureRegistrationApi;

/**
 * UserRegistered — Domain event emitted after successful user registration.
 *
 * Past-tense fact. Plain readonly object.
 * No EventInterface required.
 *
 * This is dogfooding proof only — not a reusable event contract.
 */
final readonly class UserRegistered
{
    public function __construct(
        public string $userId,
        public string $email,
        public string $registeredAt,
    ) {
    }
}

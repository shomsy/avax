<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\Register;

use SensitiveParameter;

/**
 * Value object representing user registration data.
 *
 * Banal: The data needed to create a new user.
 */
final readonly class RegistrationData
{
    public function __construct(
        #[SensitiveParameter]
        public string  $email,
        public string  $username,
        #[SensitiveParameter]
        public string  $password,
        #[SensitiveParameter]
        public string|null $ipAddress = null,
        public string|null $userAgent = null,
    ) {}
}

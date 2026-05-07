<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Flows\Login;

use SensitiveParameter;

/**
 * Value object representing user login credentials.
 *
 * Banal: Data required to log in.
 */
final readonly class Credentials
{
    public function __construct(
        public string  $identifier,
        // email or username
        #[SensitiveParameter]
        public string  $password,
        #[SensitiveParameter]
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    ) {}
}

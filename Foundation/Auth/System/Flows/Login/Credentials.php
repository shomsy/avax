<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Login;

use SensitiveParameter;

/**
 * Value object representing user login credentials.
 *
 * Banal: Data required to log in.
 */
final readonly class Credentials
{
    public function __construct(
        public string                            $identifier,
        // email or username
        #[SensitiveParameter] public string      $password,
        #[SensitiveParameter] public string|null $ipAddress = null,
        public string|null                       $userAgent = null
    )
    {
    }
}

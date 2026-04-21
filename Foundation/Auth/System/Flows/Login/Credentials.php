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
    public string|null $userAgent;
    public string|null $ipAddress;
    public string      $password;
    public string      $identifier;

    public function __construct(
        string                            $identifier, // email or username
        #[SensitiveParameter] string      $password,
        #[SensitiveParameter] string|null $ipAddress = null,
        string|null                       $userAgent = null
    )
    {
        $this->identifier = $identifier;
        $this->password   = $password;
        $this->ipAddress  = $ipAddress;
        $this->userAgent  = $userAgent;
    }
}

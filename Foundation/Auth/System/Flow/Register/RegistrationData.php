<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Register;

use SensitiveParameter;

/**
 * Value object representing user registration data.
 *
 * Banal: The data needed to create a new user.
 */
final readonly class RegistrationData
{
    public string|null $userAgent;
    public string|null $ipAddress;
    public string      $password;
    public string      $username;
    public string      $email;

    public function __construct(
        #[SensitiveParameter] string      $email,
        string                            $username,
        #[SensitiveParameter] string      $password,
        #[SensitiveParameter] string|null $ipAddress = null,
        string|null                       $userAgent = null
    )
    {
        $this->email     = $email;
        $this->username  = $username;
        $this->password  = $password;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
    }
}

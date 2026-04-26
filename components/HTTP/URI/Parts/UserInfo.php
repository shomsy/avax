<?php

declare(strict_types=1);

namespace Avax\HTTP\URI\Parts;

use SensitiveParameter;

/**
 * Represents URI user info (user and password).
 */
final readonly class UserInfo
{
    private string      $user;
    private string|null $password;

    public function __construct(string $user, #[SensitiveParameter] string|null $password = null)
    {
        $this->user     = $user;
        $this->password = $password;
    }

    public function user() : string
    {
        return $this->user;
    }

    public function password() : string|null
    {
        return $this->password;
    }

    public function __toString() : string
    {
        return $this->user . ($this->password !== null ? ':' . $this->password : '');
    }
}
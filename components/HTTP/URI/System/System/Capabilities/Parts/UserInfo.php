<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\URI\System\System\Capabilities\Parts;

use SensitiveParameter;
use Stringable;

final readonly class UserInfo implements Stringable
{
    public function __construct(
        private string  $user,
        #[SensitiveParameter]
        private ?string $password = null,
    ) {}

    public function user() : string
    {
        return $this->user;
    }

    public function password() : ?string
    {
        return $this->password;
    }

    public function __toString() : string
    {
        if ($this->user === '') {
            return '';
        }

        return $this->user . ($this->password !== null ? ':' . $this->password : '');
    }
}

<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Passwords;

use Avax\Components\Identity\Foundation\Values\PasswordHash;
use Avax\Components\Identity\Foundation\Values\PlainPassword;

interface HashPassword
{
    public function hash(PlainPassword $password): PasswordHash;
}

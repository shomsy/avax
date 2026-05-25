<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Passwords;

use Avax\Components\Identity\Foundation\Values\PasswordHash;
use Avax\Components\Identity\Foundation\Values\PlainPassword;

final class NativePasswordHashing implements HashPassword, VerifyPasswordHash
{
    public function hash(PlainPassword $password): PasswordHash
    {
        return PasswordHash::fromString(password_hash($password->exposeForHashingOnly(), PASSWORD_DEFAULT));
    }

    public function verify(PlainPassword $password, PasswordHash $hash): bool
    {
        return password_verify($password->exposeForHashingOnly(), $hash->toString());
    }
}

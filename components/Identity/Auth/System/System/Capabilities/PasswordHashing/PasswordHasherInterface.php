<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Capabilities\PasswordHashing;

/**
 * PasswordHasherInterface - Contract for secure password hashing.
 * 1:1 alignment with refactor.md.
 */
interface PasswordHasherInterface
{
    public function hash(string $password) : string;

    public function verify(string $password, string $hash) : bool;

    public function needsRehash(string $hash) : bool;
}

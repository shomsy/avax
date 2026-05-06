<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\PasswordHashing;

/**
 * PasswordHash - Value object for a hashed password.
 * 1:1 alignment with refactor.md.
 */
final readonly class PasswordHash
{
    public function __construct(
        public string $value,
    ) {
    }
}

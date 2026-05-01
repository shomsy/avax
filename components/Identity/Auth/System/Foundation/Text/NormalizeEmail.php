<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Foundation\Text;

final class NormalizeEmail
{
    public function __invoke(string $value): string
    {
        return mb_strtolower(string: trim(string: $value));
    }
}

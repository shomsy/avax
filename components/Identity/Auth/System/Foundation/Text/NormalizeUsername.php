<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Foundation\Text;

final class NormalizeUsername
{
    public function __invoke(string $value): string
    {
        return mb_strtolower(string: trim(string: preg_replace(pattern: '/\s+/', replacement: ' ', subject: $value) ?? $value));
    }
}

<?php

declare(strict_types=1);

namespace Avax\Components\Security\Secrets\System\Flows\RedactSecret;

final readonly class RedactSecret
{
    public function redact(string $value): string
    {
        if (strlen($value) <= 4) {
            return '****';
        }

        return substr($value, 0, 2).str_repeat('*', strlen($value) - 4).substr($value, -2);
    }
}

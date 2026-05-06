<?php

declare(strict_types=1);

namespace Avax\Components\Application\Text\System\Capabilities\Validate;

final class ValidateEmail
{
    public function __invoke(string $value): bool
    {
        return preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $value) === 1;
    }
}

<?php

declare(strict_types=1);

namespace Avax\Text\System\Capabilities\Validate;

final class ValidateAlphanumeric
{
    public function __invoke(string $value) : bool
    {
        return preg_match('/^[a-zA-Z0-9]+$/', $value) === 1;
    }
}
<?php

declare(strict_types=1);

namespace Avax\Text\System\Capabilities\Validate;

final class ValidateDigits
{
    public function __invoke(string $value) : bool
    {
        return preg_match('/^\d+$/', $value) === 1;
    }
}
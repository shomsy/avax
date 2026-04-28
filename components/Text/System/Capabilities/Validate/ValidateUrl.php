<?php

declare(strict_types=1);

namespace Avax\Text\System\Capabilities\Validate;

final class ValidateUrl
{
    public function __invoke(string $value) : bool
    {
        return preg_match('/^https?:\/\/[^\s\/$.?#].[^\s]*$/', $value) === 1;
    }
}
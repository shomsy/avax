<?php

declare(strict_types=1);

namespace Avax\Components\Application\Text\System\Capabilities\Validate;

final class ValidateSlug
{
    public function __invoke(string $value) : bool
    {
        return preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value) === 1;
    }
}
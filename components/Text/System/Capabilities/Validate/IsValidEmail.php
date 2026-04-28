<?php

declare(strict_types=1);

namespace Avax\Components\Text\System\Capabilities\Validate;

/**
 * Capability to validate email addresses.
 */
final class IsValidEmail
{
    public function execute(string $value) : bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $value);
    }
}

<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Headers;

use InvalidArgumentException;

final class ValidateHeaderName
{
    public function __invoke(string $name) : string
    {
        $trimmed = trim(string: $name);

        if ($trimmed === '') {
            throw new InvalidArgumentException(message: 'Header name cannot be empty.');
        }

        if (! preg_match(pattern: "/^[!#$%&'*+.^_`|~0-9A-Za-z-]+$/", subject: $trimmed)) {
            throw new InvalidArgumentException(message: "Invalid HTTP header name [{$name}].");
        }

        return $trimmed;
    }
}

<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Capabilities\Redirects;

use InvalidArgumentException;

final class ValidateRedirectTarget
{
    public function __invoke(string $target) : string
    {
        $trimmed = trim(string: $target);

        if ($trimmed === '') {
            throw new InvalidArgumentException(message: 'Redirect target cannot be empty.');
        }

        if (str_starts_with(haystack: $trimmed, needle: '/')) {
            return $trimmed;
        }

        if (filter_var(value: $trimmed, filter: FILTER_VALIDATE_URL) !== false) {
            return $trimmed;
        }

        throw new InvalidArgumentException(message: "Invalid redirect target [{$target}].");
    }
}

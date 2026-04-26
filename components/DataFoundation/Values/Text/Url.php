<?php

declare(strict_types=1);

namespace components\DataFoundation\Values\Text;

use components\DataFoundation\Exceptions\InvalidValueException;
use Stringable;

/**
 * Valid absolute URL.
 */
final readonly class Url implements Stringable
{
    private string $value;

    public function __construct(
        string $value,
    )
    {
        $normalized = trim($value);

        if (filter_var($normalized, FILTER_VALIDATE_URL) === false) {
            throw InvalidValueException::because(message: "Invalid URL value '{$value}'.");
        }

        $this->value = $normalized;
    }

    public function value() : string
    {
        return $this->value;
    }

    public function __toString() : string
    {
        return $this->value;
    }
}

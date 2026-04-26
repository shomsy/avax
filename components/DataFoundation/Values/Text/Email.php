<?php

declare(strict_types=1);

namespace components\DataFoundation\Values\Text;

use components\DataFoundation\Exceptions\InvalidValueException;
use Stringable;

/**
 * Email address with normalized casing.
 */
final readonly class Email implements Stringable
{
    private string $value;

    public function __construct(
        string $value,
    )
    {
        $normalized = mb_strtolower(trim($value));

        if (filter_var($normalized, FILTER_VALIDATE_EMAIL) === false) {
            throw InvalidValueException::because(message: "Invalid email value '{$value}'.");
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

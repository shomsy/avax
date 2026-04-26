<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Values\Text;

use Avax\DataFoundation\Exceptions\InvalidValueException;
use Stringable;

/**
 * String that cannot be empty after trimming.
 */
final readonly class NonEmptyString implements Stringable
{
    public function __construct(
        private string $value,
    )
    {
        if (trim($value) === '') {
            throw InvalidValueException::because(message: 'String value cannot be empty.');
        }
    }

    public function value() : string
    {
        return $this->value;
    }

    public function length() : int
    {
        return mb_strlen($this->value);
    }

    public function __toString() : string
    {
        return $this->value;
    }
}

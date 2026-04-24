<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Values\Identity;

use Avax\DataFoundation\Exceptions\InvalidValueException;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Stringable;

/**
 * Semantically validated UUID string.
 */
final readonly class Uuid implements Stringable
{
    public function __construct(
        private string $value,
    )
    {
        if (! RamseyUuid::isValid($value)) {
            throw InvalidValueException::because(message: "Invalid UUID value '{$value}'.");
        }
    }

    public static function generate() : self
    {
        return new self(value: RamseyUuid::uuid7()->toString());
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

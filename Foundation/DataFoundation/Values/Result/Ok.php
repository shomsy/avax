<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Values\Result;

use Avax\DataFoundation\Exceptions\InvalidValueException;

/**
 * Successful result.
 */
final readonly class Ok extends Result
{
    public function __construct(
        private mixed $value,
    ) {}

    public function isOk() : bool
    {
        return true;
    }

    public function unwrap() : mixed
    {
        return $this->value;
    }

    public function unwrapError() : mixed
    {
        throw InvalidValueException::because(message: 'Cannot unwrap an error from an Ok result.');
    }
}

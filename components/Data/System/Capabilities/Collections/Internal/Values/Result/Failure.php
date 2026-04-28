<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Internal\Values\Result;

use Avax\Components\Data\Exceptions\InvalidValueException;

/**
 * Failed result.
 */
final class Failure extends Result
{
    public function __construct(
        private mixed $error,
    ) {}

    public function isOk() : bool
    {
        return false;
    }

    public function unwrap() : mixed
    {
        throw InvalidValueException::because(message: 'Cannot unwrap a value from an Error result.');
    }

    public function unwrapError() : mixed
    {
        return $this->error;
    }
}

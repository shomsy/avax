<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Internal\Values\Result;

use Avax\Components\DataStack\Data\Exceptions\InvalidValueException;

/**
 * Successful result.
 */
final class Success extends Result
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

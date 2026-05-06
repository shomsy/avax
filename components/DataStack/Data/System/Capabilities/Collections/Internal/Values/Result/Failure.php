<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Internal\Values\Result;

use Avax\Components\DataStack\Data\System\Foundation\Exceptions\InvalidValueException;
use Override;

/**
 * Failed result.
 */
final class Failure extends Result
{
    public function __construct(
        private readonly mixed $error,
    ) {
    }

    #[Override]
    public function isOk(): bool
    {
        return false;
    }

    #[Override]
    public function unwrap(): mixed
    {
        throw InvalidValueException::because(message: 'Cannot unwrap a value from an Error result.');
    }

    #[Override]
    public function unwrapError(): mixed
    {
        return $this->error;
    }
}

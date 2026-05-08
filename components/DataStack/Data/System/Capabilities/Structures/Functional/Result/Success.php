<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Functional\Result;

use Avax\Components\DataStack\Data\System\Foundation\Exceptions\InvalidValueException;
use Override;

/**
 * Successful result.
 */
final class Success extends Result
{
    public function __construct(
        private readonly mixed $value,
    ) {
    }

    #[Override]
    public function isOk(): bool
    {
        return true;
    }

    #[Override]
    public function unwrap(): mixed
    {
        return $this->value;
    }

    #[Override]
    public function unwrapError(): mixed
    {
        throw InvalidValueException::because(message: 'Cannot unwrap an error from an Ok result.');
    }
}

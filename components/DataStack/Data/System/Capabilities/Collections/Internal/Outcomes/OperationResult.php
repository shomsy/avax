<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Internal\Outcomes;

/**
 * Represents the result of a data operation.
 */
final readonly class OperationResult
{
    public function __construct(
        public bool $success,
        public mixed $data = null,
        public ?string $error = null,
    ) {
    }

    public static function success(mixed $data = null): self
    {
        return new self(true, $data);
    }

    public static function failure(string $error): self
    {
        return new self(false, null, $error);
    }
}

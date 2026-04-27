<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Internal\Values\Result;

use RuntimeException;

/**
 * Represents a failed operation result.
 */
final readonly class Failure extends Result
{
    public function __construct(private mixed $error) {}

    public function isOk() : bool { return false; }

    public function unwrap() : mixed { throw new RuntimeException('Cannot unwrap value from Failure result.'); }

    public function unwrapError() : mixed { return $this->error; }
}

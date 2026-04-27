<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Internal\Values\Result;

use RuntimeException;

/**
 * Represents a successful operation result.
 */
final readonly class Success extends Result
{
    public function __construct(private mixed $value) {}

    public function isOk() : bool { return true; }

    public function unwrap() : mixed { return $this->value; }

    public function unwrapError() : mixed { throw new RuntimeException('Cannot unwrap error from Success result.'); }
}

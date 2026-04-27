<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Internal\Support;

/**
 * Small immutable carrier for state transitions that also yield a value.
 */
final readonly class OperationResult
{
    public function __construct(
        public mixed $value,
        public mixed $state,
        public bool  $changed,
    ) {}
}

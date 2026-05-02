<?php

declare(strict_types=1);

namespace Avax\Framework\System\Foundation\Result;

/**
 * Represents a failure result with code, message and context.
 */
final readonly class Failure
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public string $code,
        public string $message,
        public array $context = [],
    ) {
    }
}

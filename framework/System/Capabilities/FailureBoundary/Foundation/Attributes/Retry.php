<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes;

use Attribute;

/**
 * Retry — Declares retry behavior for failures.
 *
 * Usage:
 * #[Retry(maxAttempts: 3)]
 * #[Retry(maxAttempts: 5, backoff: 'exponential', delayMs: 100, jitter: true)]
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Retry
{
    public function __construct(
        public int $maxAttempts = 3,
        public string $backoff = 'none',
        public int $delayMs = 0,
        public bool $jitter = false,
    ) {
    }
}

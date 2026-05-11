<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes;

use Attribute;

/**
 * Timeout — Declares a timeout for the action execution.
 *
 * Usage:
 * #[Timeout(milliseconds: 1500)]
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Timeout
{
    public function __construct(
        public int $milliseconds = 1000,
    ) {
    }
}

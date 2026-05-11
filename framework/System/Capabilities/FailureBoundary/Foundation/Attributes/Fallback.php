<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes;

use Attribute;

/**
 * Fallback — Declares a fallback handler to execute when the primary action fails.
 *
 * Usage:
 * #[Fallback(UseCachedRates::class)]
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Fallback
{
    public function __construct(
        /** @var class-string */
        public string $handlerClass,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes;

use Attribute;

/**
 * Rethrow — Declares that failures should be rethrown rather than handled.
 *
 * Useful for excluding certain exceptions from being caught by other failure rules.
 *
 * Usage:
 * #[Rethrow]
 * #[Rethrow(except: [ValidationFailed::class, AuthenticationFailed::class])]
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Rethrow
{
    /**
     * @param list<class-string<\Throwable>> $except
     */
    public function __construct(
        public array $except = [],
    ) {
    }
}

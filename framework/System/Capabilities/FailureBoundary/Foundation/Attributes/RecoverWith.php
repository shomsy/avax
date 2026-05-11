<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes;

use Attribute;

/**
 * RecoverWith — Declares a recovery handler for complex failure recovery.
 *
 * Usage:
 * #[RecoverWith(RecoverUserRegistration::class)]
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class RecoverWith
{
    public function __construct(
        /** @var class-string */
        public string $handlerClass,
    ) {
    }
}

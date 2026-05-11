<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes;

use Attribute;

/**
 * ReportFailure — Declares that failures should be reported to a specific channel.
 *
 * Usage:
 * #[ReportFailure(channel: 'http')]
 * #[ReportFailure(channel: 'queue', level: 'critical')]
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class ReportFailure
{
    public function __construct(
        public string $channel = 'default',
        public string $level = 'error',
        public bool $includeStackTrace = false,
    ) {
    }
}

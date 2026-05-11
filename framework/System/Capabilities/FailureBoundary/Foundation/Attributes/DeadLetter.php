<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Foundation\Attributes;

use Attribute;

/**
 * DeadLetter — Declares that exhausted failures should be sent to a dead letter queue.
 *
 * Usage:
 * #[DeadLetter(queue: 'failed_jobs')]
 * #[DeadLetter(queue: 'failed_webhooks', metadata: ['source' => 'webhook'])]
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class DeadLetter
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $queue = 'failed',
        public array $metadata = [],
    ) {
    }
}

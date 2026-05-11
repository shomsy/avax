<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Foundation;

/**
 * FailureAction — A single action to execute when a specific exception type is caught.
 */
final readonly class FailureAction
{
    /**
     * @param array<string, mixed> $meta
     */
    public function __construct(
        public string $exceptionClass,
        public FailureDecision $decision,
        public int|null $statusCode = null,
        public string|null $messageKey = null,
        public string|null $reportChannel = null,
        public string|null $fallbackClass = null,
        public string|null $deadLetterQueue = null,
        public array $meta = [],
    ) {
    }
}

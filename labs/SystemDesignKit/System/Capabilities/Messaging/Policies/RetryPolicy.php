<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\Policies;

/**
 * Retry policy model.
 *
 * @experimental V3 labs
 */
final readonly class RetryPolicy
{
    public function __construct(
        public int    $maxRetries,
        public string $backoffStrategy,
        public int    $initialDelayMs,
        public int    $maxDelayMs,
    ) {}

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        if ($this->maxRetries < 0) {
            $errors[] = 'max_retries must be non-negative.';
        }

        if ($this->initialDelayMs <= 0) {
            $errors[] = 'initial_delay_ms must be positive.';
        }

        if ($this->maxDelayMs < $this->initialDelayMs) {
            $errors[] = 'max_delay_ms must be >= initial_delay_ms.';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }

    /**
     * Worst-case total retry time (all retries at max delay).
     */
    public function worstCaseRetryTimeMs() : int
    {
        return $this->maxRetries * $this->maxDelayMs;
    }
}

<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Capabilities\Messaging\DeadLetters;

/**
 * Dead letter queue model.
 *
 * @experimental V3 labs
 *
 * Models the dead letter queue: messages that fail processing
 * after max retries are moved to a DLQ for inspection and
 * manual reprocessing.
 */
final readonly class DeadLetterQueue
{
    public function __construct(
        public bool   $enabled,
        public int    $maxRetries,
        public int    $reprocessingWindowHours,
        public string $alertChannel,
    ) {}

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        if (! $this->enabled) {
            return ['valid' => true, 'errors' => []];
        }

        if ($this->maxRetries < 1) {
            $errors[] = 'max_retries must be >= 1.';
        }

        if ($this->reprocessingWindowHours < 1) {
            $errors[] = 'reprocessing_window_hours must be >= 1.';
        }

        if ($this->alertChannel === '') {
            $errors[] = 'alert_channel must not be empty.';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }

    /**
     * Whether a message with given retry count should be dead-lettered.
     */
    public function shouldDeadLetter(int $retryCount) : bool
    {
        return $retryCount >= $this->maxRetries;
    }
}

<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Capabilities\Messaging\Inbox;

/**
 * Inbox deduplication model.
 *
 * @experimental V3 labs
 *
 * Models the inbox pattern: incoming messages are deduplicated
 * using a message ID store to ensure at-most-once processing
 * on the consumer side.
 */
final readonly class Inbox
{
    public function __construct(
        public bool   $enabled,
        public int    $dedupWindowHours,
        public string $dedupKeyStrategy,
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

        if ($this->dedupWindowHours < 1) {
            $errors[] = 'dedup_window_hours must be >= 1.';
        }

        if ($this->dedupKeyStrategy === '') {
            $errors[] = 'dedup_key_strategy must not be empty.';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }
}

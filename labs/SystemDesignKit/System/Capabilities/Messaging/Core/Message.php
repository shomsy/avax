<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\Core;

/**
 * Message value object — typed message with metadata.
 *
 * @experimental V3 labs
 *
 * Models a single message in the system with its type,
 * name, idempotency requirements, and retry policy.
 */
final readonly class Message
{
    public function __construct(
        public string      $name,
        public MessageType $type,
        public bool        $idempotent,
        public int         $maxRetries,
        public int         $timeoutMs,
    ) {}

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        if ($this->name === '') {
            $errors[] = 'message name must not be empty.';
        }

        if ($this->maxRetries < 0) {
            $errors[] = 'max_retries must be non-negative.';
        }

        if ($this->timeoutMs <= 0) {
            $errors[] = 'timeout_ms must be positive.';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }
}

<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\Envelope;

/**
 * Message envelope — wraps a message with routing and metadata.
 *
 * @experimental V3 labs
 *
 * Models the envelope that carries a message through the system,
 * including correlation, causation, and message IDs.
 */
final readonly class MessageEnvelope
{
    public function __construct(
        public string  $messageId,
        public ?string $correlationId,
        public ?string $causationId,
        public string  $topic,
        public int     $ttlSeconds,
        public int     $version,
    ) {}

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        $errors = [];

        if ($this->messageId === '') {
            $errors[] = 'message_id must not be empty.';
        }

        if ($this->topic === '') {
            $errors[] = 'topic must not be empty.';
        }

        if ($this->ttlSeconds <= 0) {
            $errors[] = 'ttl_seconds must be positive.';
        }

        if ($this->version < 1) {
            $errors[] = 'version must be >= 1.';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }
}

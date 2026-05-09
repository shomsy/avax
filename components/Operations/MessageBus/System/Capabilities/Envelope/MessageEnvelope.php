<?php

declare(strict_types=1);

namespace Avax\Components\Operations\MessageBus\System\Capabilities\Envelope;

/**
 * Message envelope wrapping a payload with routing and tracing metadata.
 */
final readonly class MessageEnvelope
{
    /**
     * @param array<string, mixed> $body
     */
    public function __construct(
        public string $messageId,
        public string $type,
        public array $body,
        public string $correlationId = '',
        public string $causationId = '',
        public int $version = 1,
        public int $timestamp = 0,
        public string $source = '',
    ) {
    }

    /**
     * @param array<string, mixed> $body
     */
    public static function create(string $type, array $body, ?string $correlationId = null) : self
    {
        return new self(
            messageId: uniqid('msg_', true),
            type: $type,
            body: $body,
            correlationId: $correlationId ?? uniqid('corr_', true),
            timestamp: time(),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data) : self
    {
        return new self(
            messageId: $data['messageId'] ?? uniqid('msg_', true),
            type: $data['type'] ?? 'unknown',
            body: $data['body'] ?? [],
            correlationId: $data['correlationId'] ?? '',
            causationId: $data['causationId'] ?? '',
            version: $data['version'] ?? 1,
            timestamp: $data['timestamp'] ?? time(),
            source: $data['source'] ?? '',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        return [
            'messageId' => $this->messageId,
            'type' => $this->type,
            'body' => $this->body,
            'correlationId' => $this->correlationId,
            'causationId' => $this->causationId,
            'version' => $this->version,
            'timestamp' => $this->timestamp,
            'source' => $this->source,
        ];
    }

    public function withCausation(string $causationId) : self
    {
        return new self(
            messageId: $this->messageId,
            type: $this->type,
            body: $this->body,
            correlationId: $this->correlationId,
            causationId: $causationId,
            version: $this->version,
            timestamp: $this->timestamp,
            source: $this->source,
        );
    }
}

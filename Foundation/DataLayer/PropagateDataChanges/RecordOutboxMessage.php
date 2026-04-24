<?php

declare(strict_types=1);

namespace Avax\DataLayer\PropagateDataChanges;

use InvalidArgumentException;

final readonly class RecordOutboxMessage
{
    public function __construct(
        private string $defaultTopic
    ) {}

    public function describeResponsibility() : string
    {
        return 'records outbox message for transactional outbox pattern.';
    }

    public function record(
        string  $eventType,
        array   $payload,
        ?string $topic = null,
        array   $headers = []
    ) : OutboxRecordResult
    {
        if (empty($eventType)) {
            throw new InvalidArgumentException('Event type cannot be empty.');
        }

        return new OutboxRecordResult(
            messageId: bin2hex(random_bytes(16)),
            topic    : $topic ?? $this->defaultTopic,
            eventType: $eventType,
            payload  : $payload,
            headers  : $headers,
            status   : OutboxMessageStatus::PENDING,
            createdAt: microtime(true)
        );
    }

    public function toMetadata() : array
    {
        return ['default_topic' => $this->defaultTopic];
    }
}

final readonly class OutboxRecordResult
{
    public function __construct(
        public string              $messageId,
        public string              $topic,
        public string              $eventType,
        public array               $payload,
        public array               $headers,
        public OutboxMessageStatus $status,
        public float               $createdAt
    ) {}
}
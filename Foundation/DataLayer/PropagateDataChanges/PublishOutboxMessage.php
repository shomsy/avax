<?php

declare(strict_types=1);

namespace Avax\DataLayer\PropagateDataChanges;

use InvalidArgumentException;

enum OutboxMessageStatus: string
{
    case PENDING      = 'pending';
    case PUBLISHED    = 'published';
    case FAILED       = 'failed';
    case ACKNOWLEDGED = 'acknowledged';
}

final readonly class PublishOutboxMessage
{
    public function __construct(
        public string              $messageId,
        public string              $topic,
        public array               $payload,
        public OutboxMessageStatus $status,
        public int                 $retryCount,
        public float               $createdAt,
        public ?float              $publishedAt
    ) {}

    public function describeResponsibility() : string
    {
        return 'publishes outbox message to message bus.';
    }

    public static function create(string $topic, array $payload) : self
    {
        return new self(
            messageId  : bin2hex(random_bytes(16)),
            topic      : $topic,
            payload    : $payload,
            status     : OutboxMessageStatus::PENDING,
            retryCount : 0,
            createdAt  : microtime(true),
            publishedAt: null
        );
    }

    public function markPublished() : self
    {
        return new self(
            messageId  : $this->messageId,
            topic      : $this->topic,
            payload    : $this->payload,
            status     : OutboxMessageStatus::PUBLISHED,
            retryCount : $this->retryCount,
            createdAt  : $this->createdAt,
            publishedAt: microtime(true)
        );
    }

    public function incrementRetry() : self
    {
        return new self(
            messageId  : $this->messageId,
            topic      : $this->topic,
            payload    : $this->payload,
            status     : OutboxMessageStatus::FAILED,
            retryCount : $this->retryCount + 1,
            createdAt  : $this->createdAt,
            publishedAt: $this->publishedAt
        );
    }

    public function shouldRetry(int $maxRetries = 3) : bool
    {
        return $this->retryCount < $maxRetries;
    }

    public function toMetadata() : array
    {
        return [
            'message_id'   => $this->messageId,
            'topic'        => $this->topic,
            'status'       => $this->status->value,
            'retry_count'  => $this->retryCount,
            'created_at'   => $this->createdAt,
            'published_at' => $this->publishedAt,
        ];
    }
}
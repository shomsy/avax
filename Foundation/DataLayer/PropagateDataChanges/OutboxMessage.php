<?php

declare(strict_types=1);

namespace Avax\DataLayer\PropagateDataChanges;

use InvalidArgumentException;

final readonly class OutboxMessage
{
    public string              $id;
    public string              $aggregateType;
    public string              $aggregateId;
    public string              $eventType;
    public string              $eventPayload;
    public string              $causationId;
    public string              $correlationId;
    public \DateTimeImmutable  $occurredAt;
    public int                 $attempt;
    public ?string             $lastError;
    public ?\DateTimeImmutable $lastAttemptAt;
    public OutboxStatus        $status;

    private function __construct(
        string              $id,
        string              $aggregateType,
        string              $aggregateId,
        string              $eventType,
        string              $eventPayload,
        string              $causationId,
        string              $correlationId,
        \DateTimeImmutable  $occurredAt,
        int|null $attempt = null,
        ?string             $lastError = null,
        ?\DateTimeImmutable $lastAttemptAt = null,
        OutboxStatus        $status = OutboxStatus::PENDING
    )
    {
        $attempt ??= 0;
        $this->id            = $id;
        $this->aggregateType = $aggregateType;
        $this->aggregateId   = $aggregateId;
        $this->eventType     = $eventType;
        $this->eventPayload  = $eventPayload;
        $this->causationId   = $causationId;
        $this->correlationId = $correlationId;
        $this->occurredAt    = $occurredAt;
        $this->attempt       = $attempt;
        $this->lastError     = $lastError;
        $this->lastAttemptAt = $lastAttemptAt;
        $this->status        = $status;
    }

    public static function create(
        string  $aggregateType,
        string  $aggregateId,
        string  $eventType,
        array   $eventPayload,
        ?string $causationId = null,
        ?string $correlationId = null
    ) : self
    {
        if (empty(trim($aggregateType))) {
            throw new InvalidArgumentException('Aggregate type cannot be empty.');
        }

        if (empty(trim($eventType))) {
            throw new InvalidArgumentException('Event type cannot be empty.');
        }

        return new self(
            id           : self::generateId(),
            aggregateType: $aggregateType,
            aggregateId  : $aggregateId,
            eventType    : $eventType,
            eventPayload : json_encode($eventPayload, JSON_THROW_ON_ERROR),
            causationId  : $causationId ?? self::generateId(),
            correlationId: $correlationId ?? self::generateId(),
            occurredAt   : new \DateTimeImmutable()
        );
    }

    public static function fromArray(array $row) : self
    {
        return new self(
            id           : $row['id'],
            aggregateType: $row['aggregate_type'],
            aggregateId  : $row['aggregate_id'],
            eventType    : $row['event_type'],
            eventPayload : $row['event_payload'],
            causationId  : $row['causation_id'],
            correlationId: $row['correlation_id'],
            occurredAt   : new \DateTimeImmutable($row['occurred_at']),
            attempt      : (int) ($row['attempt'] ?? 0),
            lastError    : $row['last_error'] ?? null,
            lastAttemptAt: isset($row['last_attempt_at'])
                               ? new \DateTimeImmutable($row['last_attempt_at'])
                               : null,
            status       : OutboxStatus::from($row['status'] ?? 'pending')
        );
    }

    public function markAsPublished() : self
    {
        return new self(
            id           : $this->id,
            aggregateType: $this->aggregateType,
            aggregateId  : $this->aggregateId,
            eventType    : $this->eventType,
            eventPayload : $this->eventPayload,
            causationId  : $this->causationId,
            correlationId: $this->correlationId,
            occurredAt   : $this->occurredAt,
            attempt      : $this->attempt,
            lastError    : $this->lastError,
            lastAttemptAt: $this->lastAttemptAt,
            status       : OutboxStatus::PUBLISHED
        );
    }

    public function markAsFailed(string $error) : self
    {
        return new self(
            id           : $this->id,
            aggregateType: $this->aggregateType,
            aggregateId  : $this->aggregateId,
            eventType    : $this->eventType,
            eventPayload : $this->eventPayload,
            causationId  : $this->causationId,
            correlationId: $this->correlationId,
            occurredAt   : $this->occurredAt,
            attempt      : $this->attempt + 1,
            lastError    : $error,
            lastAttemptAt: new \DateTimeImmutable(),
            status       : OutboxStatus::FAILED
        );
    }

    public function toInsertSql() : string
    {
        return sprintf(
            "INSERT INTO outbox (id, aggregate_type, aggregate_id, event_type, event_payload, causation_id, correlation_id, occurred_at, status) 
             VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
            addslashes($this->id),
            addslashes($this->aggregateType),
            addslashes($this->aggregateId),
            addslashes($this->eventType),
            addslashes($this->eventPayload),
            addslashes($this->causationId),
            addslashes($this->correlationId),
            $this->occurredAt->format('Y-m-d H:i:s.u'),
            $this->status->value
        );
    }

    public function toUpdateStatusSql() : string
    {
        $lastError   = $this->lastError ? sprintf("'%s'", addslashes($this->lastError)) : 'NULL';
        $lastAttempt = $this->lastAttemptAt ? sprintf("'%s'", $this->lastAttemptAt->format('Y-m-d H:i:s.u')) : 'NULL';

        return sprintf(
            "UPDATE outbox SET status = '%s', attempt = %d, last_error = %s, last_attempt_at = %s WHERE id = '%s'",
            $this->status->value,
            $this->attempt,
            $lastError,
            $lastAttempt,
            addslashes($this->id)
        );
    }

    public function getPayload() : array
    {
        return json_decode($this->eventPayload, true, 512, JSON_THROW_ON_ERROR);
    }

    public function getIdempotencyKey() : string
    {
        return sprintf('%s:%s:%s', $this->aggregateType, $this->aggregateId, $this->eventType);
    }

    public function isRetriable() : bool
    {
        return $this->attempt < 3 && $this->status === OutboxStatus::FAILED;
    }

    private static function generateId() : string
    {
        return sprintf(
            '%s-%s-%s-%s',
            bin2hex(random_bytes(4)),
            bin2hex(random_bytes(2)),
            bin2hex(random_bytes(2)),
            bin2hex(random_bytes(6))
        );
    }
}

enum OutboxStatus: string
{
    case PENDING   = 'pending';
    case PUBLISHED = 'published';
    case FAILED    = 'failed';
    case DEAD      = 'dead';
}
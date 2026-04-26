<?php

declare(strict_types=1);

namespace Avax\DataLayer\PropagateDataChanges;

use Random\RandomException;

enum ChangeType: string
{
    case INSERT   = 'insert';
    case UPDATE   = 'update';
    case DELETE   = 'delete';
    case TRUNCATE = 'truncate';
}

enum OperationStatus: string
{
    case PENDING      = 'pending';
    case PUBLISHED    = 'published';
    case FAILED       = 'failed';
    case ACKNOWLEDGED = 'acknowledged';
}

final readonly class PublishedChange
{
    public function __construct(
        public string          $id,
        public string          $streamName,
        public ChangeType      $type,
        public string          $table,
        public array           $before,
        public array           $after,
        public array           $metadata,
        public OperationStatus $status,
        public float           $timestamp
    ) {}

    public function describeResponsibility() : string
    {
        return 'represents a published change from CDC stream.';
    }

    /**
     * @throws RandomException
     */
    public static function create(
        string     $streamName,
        ChangeType $type,
        string     $table,
        array      $before,
        array      $after,
        array      $metadata = []
    ) : self
    {
        return new self(
            id        : bin2hex(random_bytes(16)),
            streamName: $streamName,
            type      : $type,
            table     : $table,
            before    : $before,
            after     : $after,
            metadata  : $metadata,
            status    : OperationStatus::PENDING,
            timestamp : microtime(true)
        );
    }

    public function withStatus(OperationStatus $status) : self
    {
        return new self(
            id        : $this->id,
            streamName: $this->streamName,
            type      : $this->type,
            table     : $this->table,
            before    : $this->before,
            after     : $this->after,
            metadata  : $this->metadata,
            status    : $status,
            timestamp : $this->timestamp
        );
    }

    public function isDataChange() : bool
    {
        return in_array($this->type, [ChangeType::INSERT, ChangeType::UPDATE, ChangeType::DELETE], true);
    }

    public function toMetadata() : array
    {
        return [
            'id'          => $this->id,
            'stream_name' => $this->streamName,
            'type'        => $this->type->value,
            'table'       => $this->table,
            'status'      => $this->status->value,
            'timestamp'   => $this->timestamp,
        ];
    }
}
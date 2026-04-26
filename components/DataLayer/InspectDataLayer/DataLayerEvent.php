<?php

declare(strict_types=1);

namespace Avax\DataLayer\InspectDataLayer;

use Random\RandomException;

enum DataLayerEventType: string
{
    case QUERY       = 'query';
    case TRANSACTION = 'transaction';
    case CONNECTION  = 'connection';
    case ERROR       = 'error';
}

final readonly class DataLayerEvent
{
    public function __construct(
        public string             $id,
        public DataLayerEventType $type,
        public array              $metadata,
        public float              $timestamp
    ) {}

    /**
     * @throws RandomException
     */
    public static function create(DataLayerEventType $type, array $metadata = []) : self
    {
        return new self(
            id       : bin2hex(random_bytes(16)),
            type     : $type,
            metadata : $metadata,
            timestamp: microtime(true)
        );
    }

    public function describeResponsibility() : string
    {
        return 'records data layer event including type, metadata, and timestamp.';
    }

    public function toMetadata() : array
    {
        return [
            'id'        => $this->id,
            'type'      => $this->type->value,
            'metadata'  => $this->metadata,
            'timestamp' => $this->timestamp,
        ];
    }
}
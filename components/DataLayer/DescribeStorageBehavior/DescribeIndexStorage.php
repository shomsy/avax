<?php

declare(strict_types=1);

namespace Avax\DataLayer\DescribeStorageBehavior;

enum IndexStorageType: string
{
    case HASH  = 'hash';
    case BTREE = 'btree';
    case GIN   = 'gin';
    case GIST  = 'gist';
    case BRIN  = 'brin';
}

final readonly class DescribeIndexStorage
{
    public function __construct(
        public IndexStorageType $type,
        public bool             $unique,
        public int|null         $maxSizeBytes,
        public array            $includedColumns
    ) {}

    public static function btree(bool $unique = false) : self
    {
        return new self(type: IndexStorageType::BTREE, unique: $unique, maxSizeBytes: null, includedColumns: []);
    }

    public static function hash() : self
    {
        return new self(type: IndexStorageType::HASH, unique: false, maxSizeBytes: null, includedColumns: []);
    }

    public static function gin(array $includedColumns = []) : self
    {
        return new self(type: IndexStorageType::GIN, unique: false, maxSizeBytes: null, includedColumns: $includedColumns);
    }

    public function describeResponsibility() : string
    {
        return 'describes index storage type, uniqueness, size limits, and included columns.';
    }

    public function toMetadata() : array
    {
        return [
            'type'             => $this->type->value,
            'unique'           => $this->unique,
            'max_size_bytes'   => $this->maxSizeBytes,
            'included_columns' => $this->includedColumns,
        ];
    }
}
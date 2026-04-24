<?php

declare(strict_types=1);

namespace Avax\DataLayer\DescribeStorageBehavior;

enum IndexKind: string
{
    case PRIMARY    = 'primary';
    case UNIQUE     = 'unique';
    case COMPOSITE  = 'composite';
    case PARTIAL    = 'partial';
    case EXPRESSION = 'expression';
    case FULL_TEXT  = 'full_text';
    case SPATIAL    = 'spatial';
    case HASH       = 'hash';
    case INVERTED   = 'inverted';
}

final readonly class IndexStorageKind
{
    public function __construct(
        public IndexKind $kind,
        public array     $columns,
        public bool      $unique,
        public bool      $clustered,
        public ?int      $maxSizeBytes
    ) {}

    public function describeResponsibility() : string
    {
        return 'records index kind, columns, uniqueness, clustering, and size limits.';
    }

    public static function primary(array $columns) : self
    {
        return new self(IndexKind::PRIMARY, $columns, true, true, null);
    }

    public static function unique(array $columns) : self
    {
        return new self(IndexKind::UNIQUE, $columns, true, false, null);
    }

    public static function composite(array $columns) : self
    {
        return new self(IndexKind::COMPOSITE, $columns, false, false, null);
    }

    public static function fullText(array $columns) : self
    {
        return new self(IndexKind::FULL_TEXT, $columns, false, false, null);
    }

    public function toMetadata() : array
    {
        return [
            'kind'           => $this->kind->value,
            'columns'        => $this->columns,
            'unique'         => $this->unique,
            'clustered'      => $this->clustered,
            'max_size_bytes' => $this->maxSizeBytes,
        ];
    }
}
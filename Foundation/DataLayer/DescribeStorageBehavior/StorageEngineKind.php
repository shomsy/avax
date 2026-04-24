<?php

declare(strict_types=1);

namespace Avax\DataLayer\DescribeStorageBehavior;

enum StorageEngineType: string
{
    case INNO_DB   = 'innodb';
    case MY_ISAM   = 'myisam';
    case MEMORY    = 'memory';
    case COLLUMNAR = 'columnar';
    case LSM       = 'lsm';
    case ROW_STORE = 'row_store';
    case KEY_VALUE = 'key_value';
}

final readonly class StorageEngineKind
{
    public function __construct(
        public StorageEngineType $type,
        public bool              $transactional,
        public bool              $inMemory,
        public int               $maxDataSizeBytes
    ) {}

    public function describeResponsibility() : string
    {
        return 'records storage engine type, transactional support, in-memory status, and max data size.';
    }

    public static function innodb() : self
    {
        return new self(StorageEngineType::INNO_DB, true, false, PHP_INT_MAX);
    }

    public static function memory() : self
    {
        return new self(StorageEngineType::MEMORY, false, true, 1073741824);
    }

    public static function columnar() : self
    {
        return new self(StorageEngineType::COLLUMNAR, true, false, PHP_INT_MAX);
    }

    public function supportsTransactions() : bool
    {
        return $this->transactional;
    }

    public function toMetadata() : array
    {
        return [
            'type'                => $this->type->value,
            'transactional'       => $this->transactional,
            'in_memory'           => $this->inMemory,
            'max_data_size_bytes' => $this->maxDataSizeBytes,
        ];
    }
}
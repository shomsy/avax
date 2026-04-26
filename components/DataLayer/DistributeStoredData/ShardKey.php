<?php

declare(strict_types=1);

namespace components\DataLayer\DistributeStoredData;

enum ShardKeyType: string
{
    case HASH   = 'hash';
    case RANGE  = 'range';
    case DIRECT = 'direct';
}

final readonly class ShardKey
{
    public function __construct(
        public ShardKeyType $type,
        public array        $columns,
        public int|null     $shardCount
    ) {}

    public static function hash(array $columns, int $shardCount = 256) : self
    {
        return new self(type: ShardKeyType::HASH, columns: $columns, shardCount: $shardCount);
    }

    public static function range(string $column, int $shardCount = 16) : self
    {
        return new self(type: ShardKeyType::RANGE, columns: [$column], shardCount: $shardCount);
    }

    public function describeResponsibility() : string
    {
        return 'records shard key type, columns, and target shard count.';
    }

    public function calculateShard(mixed $value) : int
    {
        if ($this->type === ShardKeyType::HASH) {
            return $this->hashShard(value: $value);
        }

        if ($this->type === ShardKeyType::RANGE) {
            return $this->rangeShard(value: $value);
        }

        return 0;
    }

    private function hashShard(mixed $value) : int
    {
        $hash = is_array($value)
            ? crc32(serialize($value))
            : crc32((string) $value);

        return abs($hash) % ($this->shardCount ?? 256);
    }

    private function rangeShard(mixed $value) : int
    {
        if (is_int($value) && $this->shardCount !== null) {
            return intdiv($value, (int) ceil(PHP_INT_MAX / $this->shardCount / 2));
        }

        return 0;
    }

    public function toMetadata() : array
    {
        return [
            'type'        => $this->type->value,
            'columns'     => $this->columns,
            'shard_count' => $this->shardCount,
        ];
    }
}
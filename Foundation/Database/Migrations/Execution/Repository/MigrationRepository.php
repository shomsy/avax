<?php

declare(strict_types=1);

namespace Avax\Migrations\Execution\Repository;

use Avax\Database\QueryBuilder\Core\Builder\QueryBuilder;
use Throwable;

/**
 * Technical authority for the 'migrations' table auditing and record management.
 *
 * -- intent: centralize persistence and retrieval of migration execution history.
 */
final class MigrationRepository
{
    private string $table = 'migrations';

    public function __construct(
        private readonly QueryBuilder $builder
    ) {}

    /**
     * @throws Throwable
     */
    public function getRan(): array
    {
        return $this->builder->from(table: $this->table)
            ->select('migration', 'checksum')
            ->get();
    }

    /**
     * @throws Throwable
     */
    public function getLastBatch(int $steps = 1): array
    {
        $maxBatch = (int) $this->builder->from(table: $this->table)->max(column: 'batch');
        $minBatch = max(0, $maxBatch - $steps + 1);

        return $this->builder->from(table: $this->table)
            ->where(column: 'batch', operator: '>=', value: $minBatch)
            ->orderBy(column: 'batch', direction: 'DESC')
            ->orderBy(column: 'migration', direction: 'DESC')
            ->get();
    }

    /**
     * @throws Throwable
     */
    public function getNextBatchNumber(): int
    {
        return (int) $this->builder->from(table: $this->table)->max(column: 'batch') + 1;
    }

    /**
     * @throws Throwable
     */
    public function log(string $name, int $batch, string $checksum): void
    {
        $this->builder->from(table: $this->table)->insert(values: [
            'migration' => $name,
            'batch' => $batch,
            'checksum' => $checksum,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function remove(string $name): void
    {
        $this->builder->from(table: $this->table)->where(column: 'migration', value: $name)->delete();
    }

    /**
     * @throws Throwable
     */
    public function ensureTableExists(): void
    {
        try {
            $this->builder->from(table: $this->table)->limit(limit: 1)->get();
        } catch (Throwable $e) {
            $this->createRepository();
        }
    }

    /**
     * @throws Throwable
     */
    public function createRepository(): void
    {
        $this->builder->create(table: $this->table, callback: static function ($table) {
            $table->id();
            $table->string(name: 'migration');
            $table->integer(name: 'batch');
            $table->string(name: 'checksum')->nullable();
        });
    }
}

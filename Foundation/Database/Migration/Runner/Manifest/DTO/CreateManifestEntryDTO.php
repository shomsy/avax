<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\Manifest\DTO;

use DateTimeImmutable;
use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;
use Avax\DataHandling\Validation\Attributes\Rules\ArrayType;
use Avax\DataHandling\Validation\Attributes\Rules\Required;

/**
 * Data Transfer Object for creating a Manifest Entry.
 *
 * Provides validated, casted, and serializable structure for manifest metadata.
 *
 * @package Avax\Database\Migration\Runner\Manifest\DTO
 *
 * @final
 */
final class CreateManifestEntryDTO extends AbstractDTO
{
    /**
     * Logical migration name (e.g., CreateProductsTable).
     */
    #[Required]
    public string $migration;

    /**
     * Physical file name (e.g., 20250428120300_create_products_table.php).
     */
    #[Required]
    public string $file;

    /**
     * Migration execution status (pending, executed, rolled_back, failed).
     */
    #[Required]
    public string $status;

    /**
     * SHA-256 hash of the migration file.
     */
    #[Required]
    public string $hash;

    /**
     * Optional batch ID assigned during migration execution.
     */
    public string|null $batch = null;

    /**
     * UTC ISO8601 timestamp of execution completion.
     */
    public string|null $executed_at = null;

    /**
     * UTC ISO8601 timestamp if migration was rolled back.
     */
    public string|null $rolled_back_at = null;

    /**
     * Optional tenant identifier for multi-tenant schemas.
     */
    public string|null $tenant_id = null;

    /**
     * Categorization tags for grouping migrations.
     */
    #[ArrayType]
    public array $tags = [];

    /**
     * Execution logs attached to the migration.
     */
    #[ArrayType]
    public array $logs = [];

    /**
     * Manifest creation timestamp.
     */
    #[Required]
    public DateTimeImmutable $created_at;
}

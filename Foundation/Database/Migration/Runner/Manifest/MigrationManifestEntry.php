<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\Manifest;

use DateTimeImmutable;
use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;
use Avax\DataHandling\Validation\Attributes\Rules\ArrayType;
use Avax\DataHandling\Validation\Attributes\Rules\DateFormat;
use Avax\DataHandling\Validation\Attributes\Rules\Required;
use Avax\DataHandling\Validation\Attributes\Rules\StringType;

/**
 * Represents an immutable manifest entry for database migrations within the system.
 *
 * This Value Object encapsulates all metadata related to a single database migration,
 * including its execution status, timing information, and associated metadata. It follows
 * the immutability principle to ensure data consistency throughout the migration process.
 *
 * @package Avax\Database\Migration\Runner\Manifest
 * @final   This class is immutable and must not be extended to maintain invariants
 */
final class MigrationManifestEntry extends AbstractDTO
{
    /**
     * The unique identifier/name of the migration.
     *
     * @var string Represents the unique name used to identify this migration
     */
    #[Required]
    #[StringType]
    public string $migrationName;

    /**
     * The physical file name containing the migration code.
     *
     * @var string The actual filename on the filesystem containing migration logic
     */
    #[Required]
    #[StringType]
    public string $fileName;

    /**
     * Current status of the migration (e.g., 'pending', 'executed', 'failed').
     *
     * @var string Indicates the current state of migration execution
     */
    #[Required]
    #[StringType]
    public string $status;

    /**
     * Cryptographic hash of the migration content for integrity verification.
     *
     * @var string SHA-256 hash (or similar) of the migration file content
     */
    #[Required]
    #[StringType]
    public string $hash;

    /**
     * Optional batch identifier grouping related migrations.
     *
     * @var string|null Identifier for grouping migrations in execution batches
     */
    public string|null $batch = null;

    /**
     * Timestamp when the migration was successfully executed.
     *
     * @var string|null ISO-8601 formatted datetime string of execution
     */
    public string|null $executedAt = null;

    /**
     * Timestamp when the migration was rolled back.
     *
     * @var string|null ISO-8601 formatted datetime string of rollback
     */
    public string|null $rolledBackAt = null;

    /**
     * Optional tenant identifier for multi-tenant environments.
     *
     * @var string|null Unique identifier of the tenant this migration applies to
     */
    public string|null $tenantId = null;

    /**
     * Collection of tags for migration categorization and filtering.
     *
     * @var array<string> List of tags associated with this migration
     */
    #[ArrayType]
    public array $tags = [];

    /**
     * Execution logs and debug information.
     *
     * @var array<string, mixed> Collection of log entries related to migration execution
     */
    #[ArrayType]
    public array $logs = [];

    /**
     * Timestamp when this manifest entry was created.
     *
     * @var DateTimeImmutable Immutable datetime representing creation timestamp
     */
    #[Required]
    #[DateFormat('Y-m-d H:i:s')]
    public DateTimeImmutable $createdAt;
}
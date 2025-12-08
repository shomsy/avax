<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\Generators\Migration;

use DateTimeImmutable;
use Avax\Database\Migration\Design\Mapper\FieldToDslMapperInterface;
use Avax\Database\Migration\Design\Table\Table;
use Avax\Database\Migration\Runner\DTO\FieldDTO;
use Avax\Database\Migration\Runner\DTO\MigrationDTO;
use Avax\Database\Migration\Runner\Enum\MigrationStatus;
use Avax\Database\Migration\Runner\Generators\AbstractGenerator;
use Avax\Database\Migration\Runner\Manifest\DTO\CreateManifestEntryDTO;
use Avax\Database\Migration\Runner\Manifest\ManifestStoreInterface;
use RuntimeException;

/**
 * Generates migration classes using Avax's Domain-Specific Language (DSL).
 *
 * This generator is responsible for transforming structured migration metadata (DTOs)
 * into concrete PHP migration classes. It implements a robust templating system
 * to ensure consistent and maintainable migration file generation.
 *
 * @final    This class is final to prevent inheritance and maintain encapsulation
 * @package  Avax\Database\Migration\Runner\Generators\Migration
 * @since    8.3.0
 */
final class MigrationGenerator extends AbstractGenerator
{
    /**
     * Template file name used for generating migration classes.
     *
     * This constant defines the stub file that serves as a template for all
     * generated migration classes.
     *
     * @var string
     */
    private const string MIGRATION_STUB = 'anonymous-migration.stub';

    /**
     * Constructs a new instance of the migration generator.
     *
     * This constructor implements the Constructor Promotion pattern (PHP 8.0+) for a cleaner,
     * more maintainable dependency injection. It follows Domain-Driven Design principles
     * by accepting a mapper strategy that encapsulates the field-to-DSL mapping logic.
     *
     * @param FieldToDslMapperInterface $mapper Strategy pattern implementation responsible for
     *                                          mapping field definitions to DSL representations
     *
     * @throws \InvalidArgumentException If the mapper implementation is invalid
     *
     * @since 8.3.0
     */
    public function __construct(
        private readonly FieldToDslMapperInterface $mapper,
        private readonly ManifestStoreInterface    $manifestStore,
    ) {}

    /**
     * Orchestrates the creation of a new database migration file.
     *
     * This method serves as the primary entry point for migration generation,
     * implementing the Command pattern through DTO-based input. It delegates the
     * actual file writing to specialized private methods, maintaining separation
     * of concerns.
     *
     * @param MigrationDTO $dto Data Transfer Object containing migration specifications
     *                          including name, table, and schema information
     *
     * @return void
     * @throws \ReflectionException
     * @throws \SleekDB\Exceptions\IOException
     * @throws \SleekDB\Exceptions\InvalidArgumentException
     */

    public function generateMigration(MigrationDTO $dto) : void
    {
        // Step 1: Generate migration file and obtain its full path
        $filePath = $this->writeMigrationFile(
            fileName: $dto->name,
            table   : $dto->table,
            fields  : $dto->schema->fields
        );

        // Step 2: Build Manifest Entry DTO
        $manifestEntryDTO = new CreateManifestEntryDTO(
            [
                'migration'      => $dto->name,
                'file'           => basename($filePath),
                'status'         => MigrationStatus::Pending->value,
                'hash'           => hash_file('sha256', $filePath),
                'batch'          => null,
                'executed_at'    => null,
                'rolled_back_at' => null,
                'tenant_id'      => null,
                'tags'           => [],
                'logs'           => [],
                'created_at'     => new DateTimeImmutable(),
            ]
        );

        // Step 3: Store manifest entry
        $this->manifestStore->createEntry($manifestEntryDTO);

        // Step 4: Provide user feedback
        echo "🛠️ Migration '{$dto->name}' and manifest entry created successfully.\n";
    }


    /**
     * Handles the core migration file generation process.
     *
     * This method orchestrates the complete workflow of creating a new database migration file:
     * 1. Validates and retrieves configuration settings
     * 2. Generates the necessary file naming parts
     * 3. Prepares content placeholders
     * 4. Generates and writes the final migration file
     *
     * @param string     $fileName The base name for the migration class (PascalCase)
     * @param string     $table    The target database table name
     * @param FieldDTO[] $fields   Collection of field specifications for table schema
     *
     */
    private function writeMigrationFile(
        string $fileName,
        string $table,
        array  $fields
    ) : string {
        // Retrieve critical configuration settings for migration generation
        $namespace = config(key: 'app.namespaces.Migrations');
        $path      = config(key: 'app.paths.Migrations');

        // Validate configuration presence to ensure proper setup
        if (! ($namespace && $path)) {
            throw new RuntimeException(
                message: "Migration paths or namespaces are misconfigured."
            );
        }

        // Generate timestamp for unique migration file naming
        $timestamp = $this->generateTimestamp();

        // Transform file name into appropriate formats for different uses
        $className = ucfirst($fileName);
        $snakeName = $this->toSnakeCase(string: $fileName);

        // Prepare template placeholders with migration-specific values
        $placeholders = [
            'MigrationName' => $className,
            'Namespace'     => $namespace,
            'TableName'     => $table,
            'Fields'        => $this->generateMigrationTableFields(fields: $fields),
        ];

        // Generate migration content by applying placeholders to the template
        $stubContent = $this->replacePlaceholders(
            stub        : $this->getStub(stubName: self::MIGRATION_STUB),
            placeholders: $placeholders
        );

        // Construct the final file path for the migration
        $finalPath = $this->resolvePath(
            namespace: $namespace,
            name     : "{$timestamp}_{$snakeName}"
        );

        // Write the migration file to the filesystem
        $this->writeToFile(
            path   : $finalPath,
            content: $stubContent
        );

        // Store the generated file name important for manifest entry
        return $finalPath;
    }

    /**
     * Generates a UTC-based timestamp for migration naming.
     *
     * Creates a standardized timestamp format used in migration file names
     * to ensure proper ordering and uniqueness.
     *
     * @return string Formatted timestamp (YmdHis)
     */
    private function generateTimestamp() : string
    {
        return (new DateTimeImmutable())->format(format: 'YmdHis');
    }

    /**
     * Converts PascalCase/camelCase strings to snake_case.
     *
     * Implements a robust string transformation algorithm that handles:
     * - PascalCase to snake_case
     * - camelCase to snake_case
     * - Special character replacement
     *
     * @param string $string The input string to convert
     *
     * @return string The snake_case representation
     */
    private function toSnakeCase(string $string) : string
    {
        $string = preg_replace('/([a-z])([A-Z])/', '$1_$2', $string);
        $string = preg_replace('/[^a-zA-Z0-9]/', '_', $string);

        return strtolower(trim((string) $string, '_'));
    }

    /**
     * Generates the migration table DSL lines using the Table DSL Renderer.
     *
     * Uses a temporary Table blueprint to apply FieldDTO definitions via the fieldMapper,
     * and renders them as `$table->...` PHP migration code lines suitable for stub injection.
     *
     * @param array<int, FieldDTO> $fields Validated list of field DTOs
     *
     * @return string DSL-compatible PHP migration body
     *
     * @throws RuntimeException When the field collection is empty or rendering fails
     */
    private function generateMigrationTableFields(array $fields) : string
    {
        // Create blueprint with injected mapper (injected earlier in MigrationGenerator)
        $table = Table::create(name: 'temporary')
            ->useMapper($this->mapper)
            ->applyMany($fields);

        // Render DSL output
        return $table->toDsl();
    }

}
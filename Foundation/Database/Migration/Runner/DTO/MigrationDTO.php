<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\DTO;

use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;
use Avax\DataHandling\Validation\Attributes\Rules\DTOObjectOf;
use Avax\DataHandling\Validation\Attributes\Rules\Required;
use Avax\DataHandling\Validation\Attributes\Rules\StringType;
use Avax\DataHandling\Validation\Attributes\Rules\Trimmed;

/**
 * Data Transfer Object (DTO) for handling migration creation requests.
 *
 * This class acts as an intermediate structure for carrying data between
 * different layers/domain boundaries. It validates input data and ensures all
 * necessary properties conform to their expected types or constraints.
 *
 * Each property of the DTO is initialized and validated through the parent
 * `AbstractDTO` class's constructor.
 *
 * @package Application\DTO
 */
class MigrationDTO extends AbstractDTO
{
    /**
     * The name of the migration class (in PascalCase format).
     *
     * - This represents the high-level name of the migration and is expected to follow coding standards.
     * - This property is subject to trimming and validation rules for string-based input.
     *
     * Example:
     * ```
     * $migrationDTO->name = 'CreateUsersTable';
     * ```
     *
     * @var string Represents the name of the migration class.
     */
    #[Trimmed]   // Ensures the value is trimmed before assignment.
    #[StringType] // Validates that the value must be of type string.
    #[Required]
    public string $name;

    /**
     * The name of the database table being targeted or created by the migration.
     *
     * - This represents the physical table name in the database schema.
     * - It undergoes trimming and validation (must be a non-empty string).
     *
     * Example:
     * ```
     * $migrationDTO->table = 'users';
     * ```
     *
     * @var string Represents the target database table for the migration.
     */
    #[Trimmed]   // Ensures the value is trimmed before assignment.
    #[StringType] // Validates that the value must be a non-empty string.
    #[Required]
    public string $table;

    /**
     * The schema property representing a complex structure for validation.
     *
     * @var SchemaDTO A data transfer object containing structured schema information.
     */
    #[Required]
    #[DTOObjectOf(SchemaDTO::class)]
    public SchemaDTO $schema;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
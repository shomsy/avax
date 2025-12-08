<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\Commands;

use Avax\Database\Migration\Runner\Generators\{Repository\RepositoryGenerator};
use Avax\Database\Migration\Runner\Generators\CommandInterface;
use Avax\Database\Migration\Runner\Generators\DTO\DtoGenerator;
use Avax\Database\Migration\Runner\Generators\Entity\EntityGenerator;
use Avax\Database\Migration\Runner\Generators\Entity\EntityQueryBuilderGenerator;
use Avax\Database\Migration\Runner\Generators\Migration\MigrationGenerator;
use Avax\Database\Migration\Runner\Generators\Service\ServiceGenerator;
use Avax\Database\Migration\Runner\Service\MigrationStateManager;
use Avax\DataHandling\ArrayHandling\Arrhae;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * MakeMigrationCommand
 *
 * This class manages the creation of migrations and optionally generates
 * related components like Entity, DTO, Repository, etc.
 */
final readonly class MakeMigrationCommand implements CommandInterface
{
    private const string        ERROR_MISSING_ARGUMENTS = "Migration name and table name are required.";

    private const        string ERROR_INVALID_FIELDS    = "Invalid fields format. Expected format: 'name:type:attr1,attr2'.";

    public function __construct(
        private MigrationGenerator          $migrationGenerator,
        private EntityGenerator             $entityGenerator,
        private EntityQueryBuilderGenerator $entityQueryBuilderGenerator,
        private DtoGenerator                $dtoGenerator,
        private RepositoryGenerator         $repositoryGenerator,
        private ServiceGenerator            $serviceGenerator,
        private MigrationStateManager       $migrationStateManager,
        private LoggerInterface             $logger
    ) {}

    /**
     * Executes the MakeMigration command.
     *
     * @param array $arguments Command-line arguments.
     */
    public function execute(array $arguments) : void
    {
        try {
            $input = new Arrhae($arguments);

            // Check for presence
            if (! $input->has(key: 'name') || ! $input->has(key: 'table')) {
                $this->reportError(message: self::ERROR_MISSING_ARGUMENTS);

                return;
            }

            $name  = $input->get(key: 'name');
            $table = $input->get(key: 'table');

            $fieldsInput = $input->get(key: 'fields', default: '');
            $fields      = $this->extractFields(fieldsInput: $fieldsInput);

            $this->generateMigration(name: $name, table: $table, fields: $fields);

            if ($input->get(key: 'entity', default: false)) {
                $this->generateEntity(name: $table, fields: $fields);
            }
            if ($input->get(key: 'entity-qb', default: false)) {
                $this->generateQueryBuilder(name: $table, table: $table, fields: $fields);
            }
            if ($input->get(key: 'dto', default: false)) {
                $this->generateDto(name: $table, fields: $fields);
            }
            if ($input->get(key: 'repository', default: false)) {
                $this->generateRepository(name: $table, fields: $fields);
            }
            if ($input->get(key: 'service', default: false)) {
                $this->generateService(name: $table);
            }
        } catch (Throwable $e) {
            $this->handleException(e: $e);
        }
    }

    /**
     * Reports an error to the logger and echoes it.
     *
     * @param string $message The error message.
     */
    private function reportError(string $message) : void
    {
        $this->logger->error(message: $message);
        echo "Error: " . $message . "\n";
    }

    private function extractFields(string $fieldsInput) : array
    {
        // Wrap the fields into an Arrhae instance
        return (new Arrhae(items: explode(',', $fieldsInput)))
            ->filter(callback: fn($field) => ! empty($field)) // Filter out empty fields
            ->map(callback: function ($field) {
                $parts = explode(':', $field);
                if (count($parts) < 2) {
                    throw new InvalidArgumentException(message: self::ERROR_INVALID_FIELDS);
                }

                $name       = $parts[0];
                $type       = $parts[1];
                $attributes = array_slice($parts, 2);

                return $this->parseField(name: $name, type: $type, attributes: $attributes);
            })
            ->toArray(); // Convert back to a standard array
    }

    private function parseField(string $name, string $type, array $attributes) : array
    {
        // Wrap attributes in Arrhae for simplified handling
        return (new Arrhae(items: $attributes))
            ->reduce(
                callback: fn($fieldData, $attribute) => match (true) {
                    str_contains($attribute, 'default:') => array_merge(
                        $fieldData,
                        [
                            'default' => str_replace(
                                'default:',
                                '',
                                $attribute
                            ),
                        ]
                    ),
                    $attribute === 'unique'              => array_merge($fieldData, ['unique' => true]),
                    $attribute === 'nullable'            => array_merge($fieldData, ['nullable' => true]),
                    default                              => $fieldData
                },
                initial : ['name' => $name, 'type' => $type]
            );
    }

    private function generateMigration(string $name, string $table, array $fields) : void
    {
        try {
            $this->migrationGenerator->writeMigrationFile(name: $name, table: $table, fields: $fields);
            $this->migrationStateManager->migrate(availableMigrations: [$name]);
        } catch (Throwable $e) {
            $this->handleException(e: $e);
        }
    }

    private function handleException(Throwable $e) : void
    {
        $errorMessage = sprintf(
            'Error: %s in %s on line %d',
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
        $this->logger->error(message: $errorMessage);
        echo $errorMessage . "\n";
    }

    private function generateEntity(string $name, array $fields) : void
    {
        $this->executeSafely(
            operation     : fn() => $this->entityGenerator->create(tableName: $name, fields: $fields),
            successMessage: sprintf('Entity %s created successfully.', $name)
        );
    }

    private function executeSafely(callable $operation, string $successMessage) : void
    {
        try {
            $operation();
            $this->logger->info(message: $successMessage);
            echo $successMessage . "\n";
        } catch (Throwable $throwable) {
            $this->handleException(e: $throwable);
        }
    }

    private function generateQueryBuilder(string $name, string $table, array $fields) : void
    {
        $this->executeSafely(
            operation     : fn() => $this->entityQueryBuilderGenerator->create(
                name  : $name,
                table : $table,
                fields: $fields
            ),
            successMessage: sprintf('Entity QueryBuilder %s created successfully.', $name)
        );
    }

    private function generateDto(string $name, array $fields) : void
    {
        $this->executeSafely(
            operation     : fn() => $this->dtoGenerator->create(tableName: $name, fields: $fields),
            successMessage: sprintf('DTO %s created successfully.', $name)
        );
    }

    private function generateRepository(string $name, array $fields) : void
    {
        $this->executeSafely(
            operation     : fn() => $this->repositoryGenerator->create(
                tableName: $name,
                entity   : $name,
                fields   : $fields
            ),
            successMessage: sprintf('Repository %s created successfully.', $name)
        );
    }

    private function generateService(string $name) : void
    {
        $this->executeSafely(
            operation     : fn() => $this->serviceGenerator->create(name: $name),
            successMessage: sprintf('Service %s created successfully.', $name)
        );
    }
}

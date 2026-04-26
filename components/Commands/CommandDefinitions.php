<?php

declare(strict_types=1);

namespace Avax\Commands;

use Avax\Commands\App\MakeControllerCommand;
use Avax\Commands\App\MakeRepositoryCommand;
use Avax\Database\Integrations\Console\ExportCommand;
use Avax\Database\Integrations\Console\MakeMigrationCommand;
use Avax\Database\Integrations\Console\MigrateCommand;
use Avax\Database\Integrations\Console\MigrateFreshCommand;
use Avax\Database\Integrations\Console\MigrateRefreshCommand;
use Avax\Database\Integrations\Console\MigrateRollbackCommand;
use Avax\Database\Integrations\Console\MigrateStatusCommand;
use Avax\Database\Integrations\Console\SeedCommand;
use Composer\Command\InstallCommand;
use Illuminate\Database\Console\Migrations\InstallCommand;

class CommandDefinitions
{
    public static function getCommandByAlias(string $alias) : array|null
    {
        foreach (self::getAllCommands() as $name => $details) {
            if ($name === $alias || ($details['alias'] ?? null) === $alias) {
                return $details;
            }
        }

        return null;
    }

    public static function getAllCommands() : array
    {
        return array_merge(
            self::getMigrationCommands(),
            self::getGeneratorCommands(),
            self::getUtilityCommands()
        );
    }

    private static function getMigrationCommands() : array
    {
        return [
            'migrate'          => [
                'alias'       => 'migrate:up',
                'description' => 'Run all pending migrations.',
                'class'       => MigrateCommand::class,
                'arguments'   => [],
                'options'     => [],
            ],
            'migrate:rollback' => [
                'alias'       => 'migrate:down',
                'description' => 'Rollback the last batch of migrations.',
                'class'       => MigrateRollbackCommand::class,
                'arguments'   => [],
                'options'     => [],
            ],
            'migrate:refresh'  => [
                'alias'       => 'migrate:reapply',
                'description' => 'Reset and rerun all migrations.',
                'class'       => MigrateRefreshCommand::class,
                'arguments'   => [],
                'options'     => [],
            ],
            'migrate:status'   => [
                'alias'       => null,
                'description' => 'Show migration status and integrity.',
                'class'       => MigrateStatusCommand::class,
                'arguments'   => [],
                'options'     => [],
            ],
            'migrate:fresh'    => [
                'alias'       => 'migrate:clean',
                'description' => 'Drop all tables and re-run all migrations.',
                'class'       => MigrateFreshCommand::class,
                'arguments'   => [],
                'options'     => [],
            ],
            'make:migration'   => [
                'alias'       => 'create:migration',
                'description' => 'Create a new migration file.',
                'class'       => MakeMigrationCommand::class,
                'arguments'   => [
                    'name' => 'The name of the migration.',
                    'path' => 'The directory where the migration should be created.',
                ],
                'options'     => [
                    '--table'  => 'The table to create or modify.',
                    '--create' => 'Create a new table migration instead of an update migration.',
                ],
            ],
            'db:seed'          => [
                'alias'       => null,
                'description' => 'Run a database seeder class.',
                'class'       => SeedCommand::class,
                'arguments'   => [
                    'seeder' => 'The fully qualified seeder class name.',
                ],
                'options'     => [],
            ],
            'db:export'        => [
                'alias'       => null,
                'description' => 'Export database schema and data to SQL.',
                'class'       => ExportCommand::class,
                'arguments'   => [
                    'path' => 'The export output directory.',
                ],
                'options'     => [
                    '--table' => 'Optional table name to export.',
                ],
            ],
        ];
    }

    private static function getGeneratorCommands() : array
    {
        return [
            'make:controller' => [
                'alias'       => null,
                'description' => 'Generate a new controller.',
                'class'       => MakeControllerCommand::class,
                'arguments'   => [
                    'name' => 'The name of the controller.',
                ],
                'options'     => [
                    '--resource' => 'Generate a resource controller.',
                ],
            ],
            'make:repository' => [
                'alias'       => null,
                'description' => 'Generate a new repository.',
                'class'       => MakeRepositoryCommand::class,
                'arguments'   => [
                    'name'   => 'The name of the repository.',
                    'entity' => 'The fully qualified entity class name.',
                ],
                'options'     => [],
            ],
        ];
    }

    private static function getUtilityCommands() : array
    {
        return [
            'install' => [
                'alias'       => null,
                'description' => 'Set up the application (e.g., create the migrations table).',
                'class'       => InstallCommand::class,
                'arguments'   => [],
                'options'     => [],
            ],
        ];
    }
}

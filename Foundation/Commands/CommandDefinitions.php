<?php

declare(strict_types=1);

namespace Avax\Commands;

use Avax\Commands\App\MakeControllerCommand;
use Avax\Commands\App\MakeRepositoryCommand;
use Avax\Database\Migration\Runner\Commands\InstallCommand;
use Avax\Database\Migration\Runner\Commands\MakeMigrationCommand;
use Avax\Database\Migration\Runner\Commands\MigrateCommand;
use Avax\Database\Migration\Runner\Commands\MigrateFreshCommand;
use Avax\Database\Migration\Runner\Commands\MigrateRefreshCommand;
use Avax\Database\Migration\Runner\Commands\MigrateRollbackCommand;
use Avax\Migrations\Execution\Console\MigrateRollbackCommand;
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
                ],
                'options'     => [
                    '--table' => 'The table to create or modify.',
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
                    'name' => 'The name of the repository.',
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

<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database\Migrations;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Migrations as MigrationsCapability;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\SeedDatabase\Seeder;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Query;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Connections;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\Transactions;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema\Schema;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class MigrationSeedSecurityTest extends TestCase
{
    public function test_rejects_non_existent_seeder_string(): void
    {
        $migrations = $this->createMigrations();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not exist');

        $migrations->seed('NonExistentSeeder');
    }

    public function test_rejects_seeder_string_not_extending_seeder(): void
    {
        $migrations = $this->createMigrations();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not extend Seeder');

        $migrations->seed(MigrationInvalidSeedTarget::class);
    }

    private function createMigrations(): MigrationsCapability
    {
        $query = (new ReflectionClass(Query::class))->newInstanceWithoutConstructor();
        $connections = (new ReflectionClass(Connections::class))->newInstanceWithoutConstructor();
        $transactions = (new ReflectionClass(Transactions::class))->newInstanceWithoutConstructor();
        $schema = (new ReflectionClass(Schema::class))->newInstanceWithoutConstructor();

        return new MigrationsCapability(
            query: $query,
            connections: $connections,
            transactions: $transactions,
            schema: $schema,
        );
    }
}

final class MigrationInvalidSeedTarget
{
    public function run(): void {}
}

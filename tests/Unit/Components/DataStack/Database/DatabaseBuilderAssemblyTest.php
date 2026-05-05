<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Design\Table\Blueprint;
use Avax\Components\DataStack\Database\System\PublicSurface\Database;
use PDO;
use PHPUnit\Framework\TestCase;

final class DatabaseBuilderAssemblyTest extends TestCase
{
    public function test_database_builder_assembles_sqlite_database_runtime(): void
    {
        $database = Database::configuration()
            ->usingConfig([
                'default' => 'sqlite',
                'connections' => [
                    'sqlite' => [
                        'driver' => 'sqlite',
                        'database' => ':memory:',
                        'name' => 'sqlite',
                        'host' => '',
                        'username' => '',
                        'password' => '',
                        'charset' => 'utf8',
                    ],
                ],
            ])
            ->ready();

        $database->schema()->dropIfExists(table: 'users', connectionName: 'sqlite');

        $database->schema()->create(
            table: 'users',
            callback: static function (Blueprint $table): void {
                $table->integer(name: 'id')->primary();
                $table->string(name: 'name', length: 120);
            },
            connectionName: 'sqlite',
        );

        $pdo = $database->connections()->pdo(name: 'sqlite');

        self::assertInstanceOf(PDO::class, $pdo);
        self::assertSame('users', self::tableName($pdo, 'users'));

        $database->schema()->dropIfExists(table: 'users', connectionName: 'sqlite');

        self::assertFalse(self::tableName($pdo, 'users'));
    }

    private static function tableName(PDO $pdo, string $table): string|false
    {
        $statement = $pdo->query(
            sprintf(
                "SELECT name FROM sqlite_master WHERE type = 'table' AND name = '%s'",
                $table,
            ),
        );

        self::assertNotFalse($statement);

        $result = $statement->fetchColumn();

        return is_string($result) ? $result : false;
    }
}

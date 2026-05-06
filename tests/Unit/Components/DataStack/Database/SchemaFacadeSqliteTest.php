<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Connections;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\ReadConnection\ReadConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Design\Table\Blueprint;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema\Schema as SchemaCapability;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\SQLiteGrammar;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Query;
use Avax\Components\DataStack\Database\System\PublicSurface\Schema;
use PDO;
use PHPUnit\Framework\TestCase;

final class SchemaFacadeSqliteTest extends TestCase
{
    public function test_schema_create_table_and_drop_if_exists_execute_against_sqlite(): void
    {
        [$schema, $connections] = self::schemaForSqliteMemory();

        $schema->dropIfExists(table: 'users', connectionName: 'sqlite');

        $schema->create(
            table         : 'users',
            callback      : static function (Blueprint $table): void {
                $table->integer(name: 'id')->primary();
                $table->string(name: 'name', length: 120);
                $table->string(name: 'email')->unique();
                $table->boolean(name: 'active')->default(value: true);
            },
            connectionName: 'sqlite',
        );

        $pdo = $connections->pdo(name: 'sqlite');

        self::assertSame(
            expected: 'users',
            actual  : $pdo
                ->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'users'")
                ->fetchColumn(),
        );

        $columnNames = self::columnNames($pdo, table: 'users');

        self::assertContains(needle: 'id', haystack: $columnNames);
        self::assertContains(needle: 'name', haystack: $columnNames);
        self::assertContains(needle: 'email', haystack: $columnNames);
        self::assertContains(needle: 'active', haystack: $columnNames);

        $schema->dropIfExists(table: 'users', connectionName: 'sqlite');

        self::assertFalse(
            condition: $pdo
                ->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'users'")
                ->fetchColumn(),
        );
    }

    public function test_schema_table_adds_column_against_sqlite(): void
    {
        [$schema, $connections] = self::schemaForSqliteMemory();

        $schema->dropIfExists(table: 'users', connectionName: 'sqlite');

        $schema->create(
            table         : 'users',
            callback      : static function (Blueprint $table): void {
                $table->integer(name: 'id')->primary();
                $table->string(name: 'name', length: 120);
            },
            connectionName: 'sqlite',
        );

        $schema->table(
            table         : 'users',
            callback      : static function (Blueprint $table): void {
                $table->string(name: 'nickname', length: 80)->nullable();
            },
            connectionName: 'sqlite',
        );

        self::assertContains(
            needle  : 'nickname',
            haystack: self::columnNames($connections->pdo(name: 'sqlite'), table: 'users'),
        );
    }

    /**
     * @return array{0: Schema, 1: Connections}
     */
    private static function schemaForSqliteMemory(): array
    {
        $readConnection = new ReadConnection(config: [
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
        ]);

        $connections = new Connections(readConnection: $readConnection);

        $query = new Query(
            connections: $connections,
            grammar    : new SQLiteGrammar(),
        );

        return [
            new Schema(schemaCapability: new SchemaCapability(query: $query)),
            $connections,
        ];
    }

    /**
     * @return list<string>
     */
    private static function columnNames(PDO $pdo, string $table): array
    {
        $columns = $pdo->query(sprintf('PRAGMA table_info(%s)', $table))->fetchAll(PDO::FETCH_ASSOC);

        return array_values(array_map(
            callback: static fn (array $column): string => (string) $column['name'],
            array   : $columns,
        ));
    }
}

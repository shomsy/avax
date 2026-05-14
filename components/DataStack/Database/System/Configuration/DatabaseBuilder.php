<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Configuration;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Connections;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\ReadConnection\ReadConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\ReadConnection\ReadPdo;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\ReadConnection\RememberConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\ReadConnection\ResolveDefaultConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\RunWithConnection\RunWithConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Migrations;
use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema\Schema;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Entities;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Hydration\Hydrator;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\IdentityMap\IdentityMap;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Metadata\AttributeMetadataReader;
use Avax\Components\DataStack\Database\System\Capabilities\ORM\Persisters\EntityPersister;
use Avax\Components\DataStack\Database\System\Capabilities\Query\CreateBuilder\CreateBuilder;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\MySQLGrammar;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Query;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\QueryEvents\EventBus;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Telemetry;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\Transactions;
use Avax\Components\DataStack\Database\System\PublicSurface\Database;
use InvalidArgumentException;
use Throwable;

/**
 * Assembles the Database component runtime from configuration.
 */
final class DatabaseBuilder
{
    /**
     * @var array<string, mixed>
     */
    private array $config = [];

    /**
     * @param  array<string, mixed>  $config
     */
    public function usingConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public function addConnection(string $name, array $config): self
    {
        $this->config['connections'][$name] = $config;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @throws Throwable
     */
    public function ready(): Database
    {
        $this->assertHasConnections();

        $eventBus = new EventBus();

        $readConnection = new ReadConnection(
            config: $this->config,
            resolveDefaultConnection: new ResolveDefaultConnection(config: $this->config),
            rememberConnection: new RememberConnection(),
            eventBus: $eventBus,
        );
        $readPdo = new ReadPdo(readConnection: $readConnection);
        $runWithConnection = new RunWithConnection(readConnection: $readConnection);
        $connections = new Connections(
            readConnection: $readConnection,
            readPdo: $readPdo,
            runWithConnection: $runWithConnection,
        );

        $query = new Query(
            connections: $connections,
            grammar: new MySQLGrammar(),
            createBuilder: new CreateBuilder(
                connections: $connections,
                grammar: new MySQLGrammar(),
                eventBus: $eventBus,
            ),
            eventBus: $eventBus,
        );

        $schema = new Schema(query: $query);

        $transactions = new Transactions(
            databaseConnection: $connections->connection(name: $this->defaultConnectionName()),
        );

        $attributeMetadataReader = new AttributeMetadataReader();
        $hydrator = new Hydrator(new IdentityMap());
        $entityPersister = new EntityPersister($query, $attributeMetadataReader, $hydrator);

        return new Database(
            connections: $connections,
            queryCapability: $query,
            entitiesCapability: new Entities($query, $attributeMetadataReader, $hydrator, $entityPersister),
            schemaCapability: $schema,
            migrationsCapability: new Migrations(
                query: $query,
                connections: $connections,
                transactions: $transactions,
                schema: $schema,
            ),
            transactionsCapability: $transactions,
            telemetryCapability: new Telemetry(eventBus: $eventBus),
        );
    }

    private function assertHasConnections(): void
    {
        if (! isset($this->config['connections']) || ! is_array($this->config['connections']) || $this->config['connections'] === []) {
            throw new InvalidArgumentException(message: 'Database configuration must contain at least one connection.');
        }
    }

    private function defaultConnectionName() : string|null
    {
        $default = $this->config['default'] ?? null;

        return is_string(value: $default) && $default !== '' ? $default : null;
    }
}

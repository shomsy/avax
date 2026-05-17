<?php

declare(strict_types=1);

namespace components\Database\System\Capabilities\Connections\Pools;

use components\Database\System\Capabilities\Query\Grammar\Dialect;
use InvalidArgumentException;

final class PoolFactory
{
    public static function createLazy(
        string $driver,
        array  $config,
        int    $maxConnections = 20,
    ) : ConnectionPoolInterface
    {
        return new LazyConnectionPool(
            config        : $config,
            maxConnections: $maxConnections,
            factory       : static fn () => self::create(driver: $driver, config: $config, minConnections: 0, maxConnections: $maxConnections),
        );
    }

    public static function create(
        string $driver,
        array  $config,
        ?int   $minConnections = null,
        int    $maxConnections = 20,
    ) : ConnectionPoolInterface
    {
        $minConnections ??= 5;

        return match (Dialect::tryFrom(value: strtolower(string: $driver))) {
            Dialect::MYSQL         => new MySQLPool(config: $config, minConnections: $minConnections, maxConnections: $maxConnections),
            Dialect::POSTGRESQL    => new PostgreSQLPool(config: $config, minConnections: $minConnections, maxConnections: $maxConnections),
            Dialect::SQLITE        => new SQLitePool(config: $config, minConnections: $minConnections, maxConnections: min(5, $maxConnections)),
            Dialect::SQLSERVER     => new SQLServerPool(config: $config, minConnections: $minConnections, maxConnections: $maxConnections),
            Dialect::MONGODB       => new MongoDBPool(config: $config, minConnections: $minConnections, maxConnections: $maxConnections),
            Dialect::REDIS         => new RedisPool(config: $config, minConnections: $minConnections, maxConnections: $maxConnections),
            Dialect::ELASTICSEARCH => new ElasticsearchPool(config: $config, minConnections: $minConnections, maxConnections: $maxConnections),
            Dialect::CASSANDRA     => new CassandraPool(config: $config, minConnections: $minConnections, maxConnections: $maxConnections),
            Dialect::NEO4J         => new Neo4jPool(config: $config, minConnections: $minConnections, maxConnections: $maxConnections),
            Dialect::CLICKHOUSE    => new ClickHousePool(config: $config, minConnections: $minConnections, maxConnections: $maxConnections),
            Dialect::COCKROACHDB   => new CockroachDBPool(config: $config, minConnections: $minConnections, maxConnections: $maxConnections),
            Dialect::YUGABYTEDB    => new YugabyteDBPool(config: $config, minConnections: $minConnections, maxConnections: $maxConnections),
            default                => throw new InvalidArgumentException(message: "Unsupported driver: {$driver}"),
        };
    }
}

<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar;

enum Dialect: string
{
    case MYSQL         = 'mysql';
    case POSTGRESQL    = 'postgresql';
    case SQLITE        = 'sqlite';
    case SQLSERVER     = 'sqlserver';
    case MONGODB       = 'mongodb';
    case REDIS         = 'redis';
    case ELASTICSEARCH = 'elasticsearch';
    case CASSANDRA     = 'cassandra';
    case NEO4J         = 'neo4j';
    case CLICKHOUSE    = 'clickhouse';
    case COCKROACHDB   = 'cockroachdb';
    case YUGABYTEDB    = 'yugabytedb';

    public function grammar() : GrammarInterface
    {
        return match ($this) {
            self::MYSQL         => new MySQLGrammar(),
            self::POSTGRESQL    => new PostgreSQLGrammar(),
            self::SQLITE        => new SQLiteGrammar(),
            self::SQLSERVER     => new SQLServerGrammar(),
            self::MONGODB       => new MongoDBGrammar(),
            self::REDIS         => new RedisGrammar(),
            self::ELASTICSEARCH => new ElasticsearchGrammar(),
            self::CASSANDRA     => new CassandraGrammar(),
            self::NEO4J         => new Neo4jGrammar(),
            self::CLICKHOUSE    => new ClickHouseGrammar(),
            self::COCKROACHDB   => new CockroachDBGrammar(),
            self::YUGABYTEDB    => new YugabyteDBGrammar(),
        };
    }

    public function supportsWindowFunctions() : bool
    {
        return match ($this) {
            self::MYSQL, self::POSTGRESQL, self::SQLITE, self::SQLSERVER, self::CLICKHOUSE, self::COCKROACHDB, self::YUGABYTEDB => true,
            self::MONGODB, self::REDIS, self::ELASTICSEARCH, self::CASSANDRA, self::NEO4J                                       => false,
            default                                                                                                             => false,
        };
    }

    public function supportsCTE() : bool
    {
        return match ($this) {
            self::MYSQL, self::POSTGRESQL, self::SQLITE, self::SQLSERVER, self::CLICKHOUSE, self::COCKROACHDB, self::YUGABYTEDB => true,
            self::MONGODB, self::REDIS, self::ELASTICSEARCH, self::CASSANDRA, self::NEO4J                                       => false,
            default                                                                                                             => false,
        };
    }

    public function supportsUpsert() : bool
    {
        return match ($this) {
            self::MYSQL, self::POSTGRESQL, self::SQLITE, self::SQLSERVER, self::MONGODB, self::REDIS, self::CLICKHOUSE, self::COCKROACHDB, self::YUGABYTEDB => true,
            self::ELASTICSEARCH, self::CASSANDRA, self::NEO4J                                                                                               => false,
            default                                                                                                                                         => false,
        };
    }

    public function type() : DatabaseType
    {
        return match ($this) {
            self::MYSQL, self::POSTGRESQL, self::SQLITE, self::SQLSERVER, self::COCKROACHDB, self::YUGABYTEDB => DatabaseType::RELATIONAL,
            self::MONGODB                                                                                     => DatabaseType::DOCUMENT,
            self::REDIS                                                                                       => DatabaseType::KEY_VALUE,
            self::ELASTICSEARCH                                                                               => DatabaseType::SEARCH,
            self::CASSANDRA                                                                                   => DatabaseType::WIDE_COLUMN,
            self::NEO4J                                                                                       => DatabaseType::GRAPH,
            self::CLICKHOUSE                                                                                  => DatabaseType::COLUMNAR,
        };
    }
}

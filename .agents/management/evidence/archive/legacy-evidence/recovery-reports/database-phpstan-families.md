# PHPStan Error Families

Input: `EVIDENCE/recovery-reports/database-phpstan.raw`

| Family                      | Count |
|-----------------------------|------:|
| missing_iterable_value_type |   275 |
| other                       |   266 |
| mixed                       |   102 |
| wrong_return                |    64 |
| unknown_method              |    42 |
| phpdoc                      |    28 |
| wrong_argument              |    20 |
| unknown_class               |     4 |
| unknown_property            |     3 |
| missing_generic             |     1 |

## Examples

### missing_generic

-
`/home/shomsy/projects/avax/components/DataStack/Database/System/PublicSurface/Entities.php:56:Method Avax\Components\DataStack\Database\System\PublicSurface\Entities::repository() return type with generic class Avax\Components\DataStack\Database\System\Capabilities\ORM\Repositories\EntityRepository does not specify its types: TEntity`

### missing_iterable_value_type

-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Advanced/BloomFilter.php:9:Property Avax\Components\DataStack\Database\System\Capabilities\Advanced\BloomFilter::$bitset type has no value type specified in iterable type array.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Advanced/DatabaseBloomFilter.php:9:Property Avax\Components\DataStack\Database\System\Capabilities\Advanced\DatabaseBloomFilter::$bitset type has no value type specified in iterable type array.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Advanced/TwoPhaseCommit.php:11:Method Avax\Components\DataStack\Database\System\Capabilities\Advanced\TwoPhaseCommit::coordinate() has parameter $participants with no value type specified in iterable type array.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/MultiTenantPool.php:13:Property Avax\Components\DataStack\Database\System\Capabilities\Connections\MultiTenantPool::$pools type has no value type specified in iterable type array.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/MultiTenantPool.php:15:Method Avax\Components\DataStack\Database\System\Capabilities\Connections\MultiTenantPool::__construct() has parameter $tenantConfigs with no value type specified in iterable type array.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/MySQLPool.php:12:Property Avax\Components\DataStack\Database\System\Capabilities\Connections\MySQLPool::$pool type has no value type specified in iterable type array.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/MySQLPool.php:14:Method Avax\Components\DataStack\Database\System\Capabilities\Connections\MySQLPool::__construct() has parameter $options with no value type specified in iterable type array.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/ArrayPooledConnection.php:18:Method Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\ArrayPooledConnection::__construct() has parameter $config with no value type specified in iterable type array.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/CassandraPool.php:16:Method Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\CassandraPool::__construct() has parameter $config with no value type specified in iterable type array.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/ClickHousePool.php:16:Method Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\ClickHousePool::__construct() has parameter $config with no value type specified in iterable type array.`

### mixed

-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Advanced/TwoPhaseCommit.php:15:Mixed variable in a `$
p->...()` can skip important errors. Make sure the type is known`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Advanced/TwoPhaseCommit.php:20:Mixed variable in a `$
participant->...()` can skip important errors. Make sure the type is known`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Advanced/TwoPhaseCommit.php:24:Mixed variable in a `$
participant->...()` can skip important errors. Make sure the type is known`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/ElasticsearchPool.php:23:Missing parameter $config (array<string, mixed>) in call to method Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\DatabaseConnectionPool::__construct().`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/Neo4jPool.php:23:Missing parameter $config (array<string, mixed>) in call to method Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\DatabaseConnectionPool::__construct().`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Migrations/MigrationEngine.php:29:Mixed variable in a `$
migration->...()` can skip important errors. Make sure the type is known`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Migrations/RunMigrations/MigrationRepository.php:94:Mixed variable in a `$
table->...()` can skip important errors. Make sure the type is known`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Migrations/RunMigrations/MigrationRepository.php:95:Mixed variable in a `$
table->...()` can skip important errors. Make sure the type is known`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Migrations/RunMigrations/MigrationRepository.php:96:Mixed variable in a `$
table->...()` can skip important errors. Make sure the type is known`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Migrations/RunMigrations/MigrationRepository.php:97:Mixed variable in a `$
table->...()` can skip important errors. Make sure the type is known`

### other

-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Connections.php:59:Missing parameter $executionScope (Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Trackers\ExecutionScope) in call to method Avax\Components\DataStack\Database\System\Capabilities\Connections\ReadConnection\ReadConnection::withScope().`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Connections.php:59:Unknown parameter $scope in call to method Avax\Components\DataStack\Database\System\Capabilities\Connections\ReadConnection\ReadConnection::withScope().`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/OpenConnection/BuildPhysicalConnection.php:58:Unknown parameter $previous in call to Avax\Components\DataStack\Database\System\Capabilities\Connections\Exceptions\ConnectionFailure constructor.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/OpenConnection/OpenConnection.php:60:Unknown parameter $scope in call to Avax\Components\DataStack\Database\System\Capabilities\Connections\OpenConnection\OpenConnection constructor.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/OpenConnection/OpenConnection.php:69:Unknown parameter $scope in call to Avax\Components\DataStack\Database\System\Capabilities\Connections\OpenConnection\OpenConnection constructor.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/BorrowedConnection.php:77:Missing parameter $databaseConnection (Avax\Components\DataStack\Database\System\Capabilities\Connections\Contracts\DatabaseConnection) in call to method Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\Contracts\ConnectionPoolInterface::release().`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/BorrowedConnection.php:77:Unknown parameter $connection in call to method Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\Contracts\ConnectionPoolInterface::release().`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/CassandraPool.php:23:Call to an undefined static method Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\BaseConnectionPool::__construct().`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/CassandraPool.php:31:Method Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\CassandraPool::createConnection() has #[\Override] attribute but does not override any method.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/CassandraPool.php:38:Method Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\CassandraPool::validateConnection() has #[\Override] attribute but does not override any method.`

### phpdoc

-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/ORM/Proxies/LazyReference.php:18:PHPDoc tag @param for parameter $loader with type (callable)|null is not subtype of native type Closure.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/ORM/Proxies/LazyReference.php:18:PHPDoc type for property Avax\Components\DataStack\Database\System\Capabilities\ORM\Proxies\LazyReference::$loader with type (callable)|null is not subtype of native type Closure.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Query/Builder/Concerns/HasConditions.php (in context of class Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder):32:PHPDoc tag @return with type Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\Concerns\HasConditions|Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder is not subtype of native type Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Query/Builder/Concerns/HasConditions.php (in context of class Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder):49:PHPDoc tag @return with type Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\Concerns\HasConditions|Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder is not subtype of native type Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Query/Builder/Concerns/HasConditions.php (in context of class Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder):102:PHPDoc tag @return with type Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\Concerns\HasConditions|Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder is not subtype of native type Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Query/Builder/Concerns/HasConditions.php (in context of class Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder):140:PHPDoc tag @return with type Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\Concerns\HasConditions|Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder is not subtype of native type Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Query/Builder/Concerns/HasConditions.php (in context of class Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder):167:PHPDoc tag @return with type Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\Concerns\HasConditions|Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder is not subtype of native type Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Query/Builder/Concerns/HasConditions.php (in context of class Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder):207:PHPDoc tag @return with type Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\Concerns\HasConditions|Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder is not subtype of native type Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Query/Builder/Concerns/HasControlStructures.php (in context of class Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder):58:PHPDoc tag @return with type Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\Concerns\HasControlStructures|Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder is not subtype of native type Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Query/Builder/Concerns/HasControlStructures.php (in context of class Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder):84:PHPDoc tag @return with type Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\Concerns\HasControlStructures|Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder is not subtype of native type Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder.`

### unknown_class

-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Migrations/LoadMigrations/MigrationLoader.php:56:Class Avax\Components\DataStack\Database\System\Capabilities\Migrations\Design\BaseMigration not found.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Migrations/LoadMigrations/MigrationLoader.php:93:Class Avax\Components\DataStack\Database\System\Capabilities\Migrations\Design\BaseMigration not found.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/QueryGovernance/QueryGovernance.php:31:Call to static method detect() on an unknown class Avax\Components\DataStack\Database\System\Capabilities\QueryGovernance\System\Capabilities\Detection\NPlusOneDetector.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/QueryGovernance/QueryGovernance.php:36:Call to static method isSlow() on an unknown class Avax\Components\DataStack\Database\System\Capabilities\QueryGovernance\System\Capabilities\Detection\SlowQueryDetector.`

### unknown_method

-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/ReadConnection/ReadConnection.php:95:Call to an undefined method Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\ConnectionPool::withScope().`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Migrations/CLI/MigrateCommand.php:36:Call to an undefined method object::up().`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Migrations/CLI/MigrateCommand.php:84:Call to an undefined method object::down().`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Migrations/CLI/MigrateCommand.php:136:Call to an undefined method Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema\SchemaBuilder::createTable().`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Migrations/CLI/MigrateCommand.php:170:Call to an undefined method object::run().`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Migrations/CLI/SchemaCommand.php:17:Call to an undefined method Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema\SchemaBuilder::createTable().`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Migrations/CLI/SeederCommand.php:24:Call to an undefined method object::run().`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Migrations/RunMigrations/MigrationRunner.php:63:Call to an undefined method Avax\Components\DataStack\Database\System\Capabilities\Transactions\Transactions::run().`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Migrations/Schema/CreateDatabase.php:22:Call to an undefined method Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder::createDatabase().`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Migrations/Schema/DropDatabase.php:22:Call to an undefined method Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder::dropDatabase().`

### unknown_property

-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/ReadConnection/ReadConnection.php:102:Access to an undefined property Avax\Components\DataStack\Database\System\Capabilities\Connections\ReadConnection\ReadConnection::$scope.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Query/Execution/QueryOrchestrator.php:95:Access to an undefined property Avax\Components\DataStack\Database\System\Capabilities\Query\Execution\QueryOrchestrator::$scope.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Query/IR/IRTransformer.php:30:Access to an undefined property Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes\CTENode::$type.`

### wrong_argument

-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/DatabaseConnectionPool.php:93:Parameter $eventBus of class Avax\Components\DataStack\Database\System\Capabilities\Connections\OpenConnection\OpenConnection constructor expects Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Events\EventBus|null, Avax\Components\Operations\MessageBus\System\Capabilities\Bus\EventBus|null given.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/ElasticsearchPool.php:38:Parameter #1 $pooledConnection (Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\PooledConnection) of method Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\ElasticsearchPool::validateConnection() is not contravariant with parameter #1 $databaseConnection (Avax\Components\DataStack\Database\System\Capabilities\Connections\Contracts\DatabaseConnection|null) of method Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\DatabaseConnectionPool::validateConnection().`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/ElasticsearchPool.php:38:Parameter #1 $pooledConnection of method Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\ElasticsearchPool::validateConnection() is required but parameter #1 $databaseConnection of method Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\DatabaseConnectionPool::validateConnection() is optional.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/Neo4jPool.php:38:Parameter #1 $pooledConnection (Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\PooledConnection) of method Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\Neo4jPool::validateConnection() is not contravariant with parameter #1 $databaseConnection (Avax\Components\DataStack\Database\System\Capabilities\Connections\Contracts\DatabaseConnection|null) of method Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\DatabaseConnectionPool::validateConnection().`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/Neo4jPool.php:38:Parameter #1 $pooledConnection of method Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\Neo4jPool::validateConnection() is required but parameter #1 $databaseConnection of method Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\DatabaseConnectionPool::validateConnection() is optional.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Migrations/CLI/MigrateCommand.php:64:Parameter #2 $subject of function preg_match expects string, string|false given.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Migrations/RunMigrations/MigrationRunner.php:40:Parameter $checksum of method Avax\Components\DataStack\Database\System\Capabilities\Migrations\RunMigrations\MigrationRunner::runMigration() expects string|null, string|false given.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Migrations/RunMigrations/MigrationRunner.php:67:Parameter $checksum of method Avax\Components\DataStack\Database\System\Capabilities\Migrations\RunMigrations\MigrationRepository::log() expects string, string|null given.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Migrations/Schema/Schema.php:29:Parameter $statements of method Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema\Schema::runStatements() expects list<string>, array<string> given.`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Migrations/Schema/Schema.php:73:Parameter $statements of method Avax\Components\DataStack\Database\System\Capabilities\Migrations\Schema\Schema::runStatements() expects list<string>, array<string> given.`

### wrong_return

-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/OpenConnection/BuildPhysicalConnection.php:24:Provide more specific return type "Avax\Components\DataStack\Database\System\Capabilities\Connections\OpenConnection\PdoConnection" over abstract one`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/CassandraPool.php:31:Provide more specific return type "Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\ArrayPooledConnection" over abstract one`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/ClickHousePool.php:31:Provide more specific return type "Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\ArrayPooledConnection" over abstract one`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/CockroachDBPool.php:35:Provide more specific return type "Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\ArrayPooledConnection" over abstract one`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/ElasticsearchPool.php:31:Provide more specific return type "Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\ArrayPooledConnection" over abstract one`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/MongoDBPool.php:31:Provide more specific return type "Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\ArrayPooledConnection" over abstract one`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/MySQLPool.php:26:Provide more specific return type "Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\ArrayPooledConnection" over abstract one`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/Neo4jPool.php:31:Provide more specific return type "Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\ArrayPooledConnection" over abstract one`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/PoolFactory.php:12:Provide more specific return type "Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\LazyConnectionPool" over abstract one`
-
`/home/shomsy/projects/avax/components/DataStack/Database/System/Capabilities/Connections/Pools/PoolFactory.php:32:Method Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\PoolFactory::create() should return Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\ConnectionPoolInterface but returns Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\CassandraPool|Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\ClickHousePool|Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\CockroachDBPool|Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\ElasticsearchPool|Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\MongoDBPool|Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\MySQLPool|Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\Neo4jPool|Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\PostgreSQLPool|Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\RedisPool|Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\SQLitePool|Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\SQLServerPool|Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\YugabyteDBPool.`

# PHPStan Error Families

Input: `EVIDENCE/recovery-reports/persistence-phpstan.raw`

| Family                      | Count |
|-----------------------------|------:|
| missing_iterable_value_type |    44 |
| other                       |    26 |
| missing_generic             |     9 |
| mixed                       |     8 |
| wrong_return                |     7 |
| wrong_argument              |     6 |
| unknown_method              |     5 |
| array_shape                 |     5 |
| phpdoc                      |     1 |

## Examples

### array_shape

-

`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/Diagnostics/DetectNPlusOneQuery.php:83:Offset 'pattern' does not exist on array{count: int, queries: array<string>, firstSeen: float, lastSeen: float}.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/Diagnostics/DetectNPlusOneQuery.php:87:Offset 'pattern' does not exist on array{count: int, queries: array<string>, firstSeen: float, lastSeen: float}.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/Diagnostics/DetectNPlusOneQuery.php:137:Offset 'pattern' does not exist on array{count: int, queries: array<string>, firstSeen: float, lastSeen: float}.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/Diagnostics/DetectNPlusOneQuery.php:141:Offset 'pattern' does not exist on array{count: int, queries: array<string>, firstSeen: float, lastSeen: float}.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/ReadOptimization/MaterializedViewReader.php:99:PHPDoc tag @implements has invalid value (MaterializedViewInterface): Unexpected token "\n ", expected '<' at offset 295 on line 10`

### missing_generic

-

`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/ReadOptimization/MaterializedViewReader.php:271:Property Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization\MaterializedViewRegistry::$views with generic class Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization\MaterializedView does not specify its types: T`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/ReadOptimization/MaterializedViewReader.php:276:Method Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization\MaterializedViewRegistry::register() has parameter $materializedView with generic class Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization\MaterializedView but does not specify its types: T`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/ReadOptimization/MaterializedViewReader.php:310:Method Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization\MaterializedViewRegistry::get() return type with generic class Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization\MaterializedView does not specify its types: T`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/ReadOptimization/MaterializedViewReader.php:324:Method Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization\MaterializedViewRegistry::staleViews() return type with generic class Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization\MaterializedView does not specify its types: T`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/ReadOptimization/MaterializedViewRegistry.php:13:Method Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization\MaterializedViewRegistry::register() has parameter $materializedView with generic class Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization\MaterializedView but does not specify its types: T`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/ReadOptimization/MaterializedViewRegistry.php:33:Method Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization\MaterializedViewRegistry::get() return type with generic class Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization\MaterializedView does not specify its types: T`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Flows/DeleteEntity/DeleteEntity.php:11:Method Avax\Components\DataStack\Persistence\System\Flows\DeleteEntity\DeleteEntity::delete() has parameter $repository with generic class Avax\Components\DataStack\Persistence\System\Capabilities\Repositories\Repository but does not specify its types: T`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Flows/FindEntity/FindEntity.php:11:Method Avax\Components\DataStack\Persistence\System\Flows\FindEntity\FindEntity::find() has parameter $repository with generic class Avax\Components\DataStack\Persistence\System\Capabilities\Repositories\Repository but does not specify its types: T`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Flows/SaveEntity/SaveEntity.php:11:Method Avax\Components\DataStack\Persistence\System\Flows\SaveEntity\SaveEntity::save() has parameter $repository with generic class Avax\Components\DataStack\Persistence\System\Capabilities\Repositories\Repository but does not specify its types: T`

### missing_iterable_value_type

-

`/home/shomsy/projects/avax/components/DataStack/Persistence/AccessPersistentData/PersistentDataRequest.php:8:Method Avax\Components\DataStack\Persistence\AccessPersistentData\PersistentDataRequest::__construct() has parameter $parameters with no value type specified in iterable type array.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/AccessPersistentData/PersistentDataResult.php:8:Method Avax\Components\DataStack\Persistence\AccessPersistentData\PersistentDataResult::__construct() has parameter $rows with no value type specified in iterable type array.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/Consistency/ConflictPair.php:12:Method Avax\Components\DataStack\Persistence\System\Capabilities\Consistency\ConflictPair::__construct() has parameter $context with no value type specified in iterable type array.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/Consistency/ConflictResolution.php:256:Method Avax\Components\DataStack\Persistence\System\Capabilities\Consistency\ConflictPair::__construct() has parameter $context with no value type specified in iterable type array.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/Consistency/ConflictResolutionResult.php:12:Method Avax\Components\DataStack\Persistence\System\Capabilities\Consistency\ConflictResolutionResult::__construct() has parameter $details with no value type specified in iterable type array.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/Consistency/ConflictResolutionResult.php:36:Method Avax\Components\DataStack\Persistence\System\Capabilities\Consistency\ConflictResolutionResult::resolved() has parameter $details with no value type specified in iterable type array.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/Consistency/EventualConsistency.php:90:Method Avax\Components\DataStack\Persistence\System\Capabilities\Consistency\ConflictResolutionResult::__construct() has parameter $details with no value type specified in iterable type array.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/Consistency/EventualConsistency.php:112:Method Avax\Components\DataStack\Persistence\System\Capabilities\Consistency\ConflictResolutionResult::resolved() has parameter $details with no value type specified in iterable type array.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/Diagnostics/SlowPersistenceQueryDetector.php:18:Method Avax\Components\DataStack\Persistence\System\Capabilities\Diagnostics\SlowPersistenceReport::__construct() has parameter $context with no value type specified in iterable type array.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/Diagnostics/SlowPersistenceQueryDetector.php:32:Method Avax\Components\DataStack\Persistence\System\Capabilities\Diagnostics\SlowPersistenceReport::create() has parameter $context with no value type specified in iterable type array.`

### mixed

-

`/home/shomsy/projects/avax/components/DataStack/Persistence/AccessPersistentData/AccessPersistentData.php:34:Mixed variable in a `$
this->databaseRuntime->query()->...()` can skip important errors. Make sure the type is known`

-

`/home/shomsy/projects/avax/components/DataStack/Persistence/AccessPersistentData/AccessPersistentData.php:34:Mixed variable in a `$this->databaseRuntime->query()->statement($
request->statement)->...()` can skip important errors. Make sure the type is known`

-

`/home/shomsy/projects/avax/components/DataStack/Persistence/AccessPersistentData/AccessPersistentData.php:34:Mixed variable in a `$this->databaseRuntime->query()->statement($
request->statement)->bindings($request->parameters)->...()` can skip important errors. Make sure the type is known`

-

`/home/shomsy/projects/avax/components/DataStack/Persistence/AccessPersistentData/AccessPersistentData.php:60:Mixed variable in a `$
this->databaseRuntime->transactions()->...()` can skip important errors. Make sure the type is known`

-

`/home/shomsy/projects/avax/components/DataStack/Persistence/CommitDataChanges/CommitDataChanges.php:23:Mixed variable in a `$
this->databaseRuntime->transactions()->...()` can skip important errors. Make sure the type is known`

-

`/home/shomsy/projects/avax/components/DataStack/Persistence/CommitDataChanges/CommitDataChanges.php:36:Mixed variable in a `$
this->databaseRuntime->transactions()->...()` can skip important errors. Make sure the type is known`

-

`/home/shomsy/projects/avax/components/DataStack/Persistence/CommitDataChanges/CommitDataChanges.php:47:Mixed variable in a `$
this->databaseRuntime->transactions()->...()` can skip important errors. Make sure the type is known`

-

`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/ReadOptimization/MaterializedViewRegistry.php:22:Mixed variable in a `$
view->...()` can skip important errors. Make sure the type is known`

### other

-

`/home/shomsy/projects/avax/components/DataStack/Persistence/AccessPersistentData/AccessPersistentData.php:42:Class Avax\Components\Persistence\System\Foundation\Failure\PersistenceFailure does not have a constructor and must be instantiated without any parameters.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/AccessPersistentData/AccessPersistentData.php:42:Invalid type Avax\Components\Persistence\System\Foundation\Failure\PersistenceFailure to throw.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/AccessPersistentData/AccessPersistentData.php:49:Class Avax\Components\Persistence\System\Foundation\Failure\PersistenceFailure does not have a constructor and must be instantiated without any parameters.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/AccessPersistentData/AccessPersistentData.php:49:Invalid type Avax\Components\Persistence\System\Foundation\Failure\PersistenceFailure to throw.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/AccessPersistentData/AccessPersistentData.php:57:Unknown parameter $callback in call to method stdClass::runDataTransaction().`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/AccessPersistentData/AccessPersistentData.php:57:Unknown parameter $connectionName in call to method stdClass::runDataTransaction().`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/AccessPersistentData/AccessPersistentData.php:62:Class Avax\Components\Persistence\System\Foundation\Failure\PersistenceFailure does not have a constructor and must be instantiated without any parameters.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/AccessPersistentData/AccessPersistentData.php:62:Invalid type Avax\Components\Persistence\System\Foundation\Failure\PersistenceFailure to throw.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/DataLayer.php:26:Class Avax\Components\DataStack\Persistence\AccessPersistentData does not have a constructor and must be instantiated without any parameters.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/DataLayer.php:27:Class Avax\Components\DataStack\Persistence\CommitDataChanges does not have a constructor and must be instantiated without any parameters.`

### phpdoc

-

`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/QueryIntent/DataQueryBuilder.php:115:PHPDoc tag @param references unknown parameter: $join`

### unknown_method

-

`/home/shomsy/projects/avax/components/DataStack/Persistence/DataLayer.php:25:Call to an undefined method Avax\Components\DataStack\Persistence\RegisterDataLayerRuntime::register().`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/UnitOfWork/PersistenceUnitOfWork.php:66:Call to an undefined method Avax\Components\DataStack\Persistence\System\Capabilities\UnitOfWork\EntityPersisterInterface::extractData().`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/UnitOfWork/PersistenceUnitOfWork.php:119:Call to an undefined method Avax\Components\DataStack\Persistence\System\Capabilities\UnitOfWork\EntityPersisterInterface::extractData().`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/UnitOfWork/UnitOfWork.php:64:Call to an undefined method Avax\Components\DataStack\Persistence\System\Capabilities\UnitOfWork\EntityPersisterInterface::extractData().`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/UnitOfWork/UnitOfWork.php:117:Call to an undefined method Avax\Components\DataStack\Persistence\System\Capabilities\UnitOfWork\EntityPersisterInterface::extractData().`

### wrong_argument

-

`/home/shomsy/projects/avax/components/DataStack/Persistence/CommitDataChanges/CommitDataChanges.php:42:Deprecated in PHP 8.4: Parameter #1 $transaction (object) is implicitly nullable via default value null.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/Diagnostics/DetectNPlusOneQuery.php:66:Parameter #1 $hash of method Avax\Components\DataStack\Persistence\System\Capabilities\Diagnostics\DetectNPlusOneQuery::generateReport() expects string, string|null given.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/QueryIntent/DataQuery.php:45:Parameter $conditions of class Avax\Components\DataStack\Persistence\System\Capabilities\QueryIntent\DataQuery constructor expects array<string, mixed>, array<int|string, mixed> given.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/QueryIntent/DataQueryBuilder.php:140:Parameter $entityType of class Avax\Components\DataStack\Persistence\System\Capabilities\QueryIntent\DataQuery constructor expects class-string|null, string|null given.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/ReadOptimization/ReadCache.php:154:Parameter #4 $tags of method Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization\ReadCache::put() expects list<string>, array given.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Flows/CompileDataQuery/CompileDataQuery.php:38:Parameter $bindings of class Avax\Components\DataStack\Persistence\System\Capabilities\QueryIntent\DataQueryPlan constructor expects array<string, mixed>, array<int, mixed> given.`

### wrong_return

-

`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/Diagnostics/QueryFingerprint.php:28:Method Avax\Components\DataStack\Persistence\System\Capabilities\Diagnostics\QueryFingerprint::pattern() should return string but returns string|null.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/Diagnostics/QueryFingerprint.php:36:Method Avax\Components\DataStack\Persistence\System\Capabilities\Diagnostics\QueryFingerprint::hash() should return string but returns string|null.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/QueryIntent/QueryResult.php:42:Method Avax\Components\DataStack\Persistence\System\Capabilities\QueryIntent\QueryResult::count() should return int but returns int|null.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/Repositories/PersistenceRepository.php:30:Method Avax\Components\DataStack\Persistence\System\Capabilities\Repositories\PersistenceRepository::findById() should return (T of object)|null but returns object|null.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/Repositories/PersistenceRepository.php:53:Method Avax\Components\DataStack\Persistence\System\Capabilities\Repositories\PersistenceRepository::findBy() should return array<T of object> but returns array<object>.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/Repositories/Repository.php:28:Method Avax\Components\DataStack\Persistence\System\Capabilities\Repositories\Repository::findById() should return (T of object)|null but returns object|null.`
-
`/home/shomsy/projects/avax/components/DataStack/Persistence/System/Capabilities/Repositories/Repository.php:50:Method Avax\Components\DataStack\Persistence\System\Capabilities\Repositories\Repository::findBy() should return array<T of object> but returns array<object>.`

# Old To New Map — persistence

Staging: `Code-Review-And-ToDo/recovery-staging/from-backup/persistence`

| Old/staged file                                                                          | Suggested new target                                                                        | Action        | Status  |
|------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------|---------------|---------|
| `components/Database/EntityManager.php`                                                  | `components/DataStack/Persistence/System/Capabilities/Mapping/EntityManager.php`            | restore/slice | pending |
| `components/Persistence/System/Capabilities/Hydration/HydratorInterface.php`             | `components/DataStack/Persistence/System/Capabilities/Hydration/HydratorInterface.php`      | restore/slice | pending |
| `components/Persistence/System/Capabilities/Hydration/ReflectionHydrator.php`            | `components/DataStack/Persistence/System/Capabilities/Hydration/ReflectionHydrator.php`     | restore/slice | pending |
| `components/Persistence/System/Capabilities/IdentityMap/IdentityMap.php`                 | `components/DataStack/Persistence/System/Capabilities/IdentityMap/IdentityMap.php`          | restore/slice | pending |
| `components/Persistence/System/Capabilities/ObjectHandling/DTO/AbstractDTO.php`          | `components/DataStack/Persistence/System/Capabilities/NeedsHumanDecision/AbstractDTO.php`   | restore/slice | pending |
| `components/Persistence/System/Capabilities/Repositories/Repository.php`                 | `components/DataStack/Persistence/System/PublicSurface/Repository.php`                      | restore/slice | pending |
| `components/Persistence/System/Capabilities/Repositories/RepositoryInterface.php`        | `components/DataStack/Persistence/System/PublicSurface/RepositoryInterface.php`             | restore/slice | pending |
| `components/Persistence/System/Capabilities/Repositories/RepositoryRegistry.php`         | `components/DataStack/Persistence/System/PublicSurface/RepositoryRegistry.php`              | restore/slice | pending |
| `components/Persistence/System/Capabilities/Repositories/RepositoryStorageInterface.php` | `components/DataStack/Persistence/System/PublicSurface/RepositoryStorageInterface.php`      | restore/slice | pending |
| `components/Persistence/System/Capabilities/UnitOfWork/EntityPersisterInterface.php`     | `components/DataStack/Persistence/System/Capabilities/Mapping/EntityPersisterInterface.php` | restore/slice | pending |
| `components/Persistence/System/Capabilities/UnitOfWork/UnitOfWork.php`                   | `components/DataStack/Persistence/System/Capabilities/UnitOfWork/UnitOfWork.php`            | restore/slice | pending |
| `components/Persistence/System/Capabilities/UnitOfWork/UnitOfWorkInterface.php`          | `components/DataStack/Persistence/System/Capabilities/UnitOfWork/UnitOfWorkInterface.php`   | restore/slice | pending |
| `components/Persistence/System/Configuration/PersistenceBuilder.php`                     | `components/DataStack/Persistence/System/PublicSurface/PersistenceBuilder.php`              | restore/slice | pending |
| `components/Persistence/System/Flows/DeleteEntity/DeleteEntity.php`                      | `components/DataStack/Persistence/System/Capabilities/Mapping/DeleteEntity.php`             | restore/slice | pending |
| `components/Persistence/System/Flows/FindEntity/FindEntity.php`                          | `components/DataStack/Persistence/System/Capabilities/Mapping/FindEntity.php`               | restore/slice | pending |
| `components/Persistence/System/Flows/FlushChanges/FlushChanges.php`                      | `components/DataStack/Persistence/System/Capabilities/ChangeTracking/FlushChanges.php`      | restore/slice | pending |
| `components/Persistence/System/Flows/SaveEntity/SaveEntity.php`                          | `components/DataStack/Persistence/System/Capabilities/Mapping/SaveEntity.php`               | restore/slice | pending |
| `components/Persistence/System/Foundation/Failure/PersistenceFailure.php`                | `components/DataStack/Persistence/System/PublicSurface/PersistenceFailure.php`              | restore/slice | pending |
| `components/Persistence/System/PublicSurface/Persistence.php`                            | `components/DataStack/Persistence/System/PublicSurface/Persistence.php`                     | restore/slice | pending |
| `components/Persistence/System/PublicSurface/PersistenceInterface.php`                   | `components/DataStack/Persistence/System/PublicSurface/PersistenceInterface.php`            | restore/slice | pending |

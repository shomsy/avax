# Worktree Hygiene

Date: 2026-05-14
Commit: 27ef0b4eb

## Pre-existing Dirty Files (classified)

| File                                                                             | Change    | Classification               |
|----------------------------------------------------------------------------------|-----------|------------------------------|
| `components/Application/Cache/System/PublicSurface/CacheFacade.php`              | Deleted   | Phase D — left for colleague |
| `components/Application/Cache/System/PublicSurface/CacheRegistry.php`            | Deleted   | Phase D — left for colleague |
| `components/Application/Cache/System/PublicSurface/CompiledCacheTarget.php`      | Deleted   | Phase D — left for colleague |
| `components/Application/Cache/System/PublicSurface/Read/CacheReadTarget.php`     | Deleted   | Phase D — left for colleague |
| `components/Application/Cache/System/PublicSurface/ReadFromCache.php`            | Deleted   | Phase D — left for colleague |
| `components/Application/Cache/System/PublicSurface/RuntimeCacheTarget.php`       | Deleted   | Phase D — left for colleague |
| `components/Application/Cache/System/PublicSurface/Read/CompiledCacheTarget.php` | Modified  | Phase D — left for colleague |
| `components/Application/Cache/System/PublicSurface/Read/RuntimeCacheTarget.php`  | Modified  | Phase D — left for colleague |
| `components/DataStack/Database/System/PublicSurface/SchemaBuilder.php`           | Deleted   | Phase D — left for colleague |
| `components/HTTP/Middleware/System/PublicSurface/Middleware.php`                 | Deleted   | Phase D — left for colleague |
| `components/DataStack/Database/System/PublicSurface/DatabaseInterface.php`       | Modified  | Phase D — left for colleague |
| `components/DeveloperTools/Testing/System/PublicSurface/Testing.php`             | Modified  | Phase D — left for colleague |
| `components/HTTP/Response/System/PublicSurface/ResponseInterface.php`            | Modified  | Phase D — left for colleague |
| `components/HTTP/Router/System/PublicSurface/Router.php`                         | Modified  | Phase D — left for colleague |
| `components/Operations/ApplicationWorkflow/System/PublicSurface/Workflow.php`    | Modified  | Phase D — left for colleague |
| `components/Operations/MessageBus/System/PublicSurface/Command.php`              | Modified  | Phase D — left for colleague |
| `components/Operations/MessageBus/System/PublicSurface/DomainEvent.php`          | Modified  | Phase D — left for colleague |
| `components/Operations/MessageBus/System/PublicSurface/Query.php`                | Modified  | Phase D — left for colleague |
| `components/HTTP/Router/System/Capabilities/RouteGroup/`                         | Untracked | Phase D — left for colleague |
| `components/Operations/ApplicationWorkflow/System/Capabilities/SagaExecutor/`    | Untracked | Phase D — left for colleague |

## Files Changed by This Pass (Phase B + C)

See git commit `27ef0b4eb` — 50 files changed: 250 insertions, 525 deletions.

## Protected Files (not to be touched)

None in this pass. Phase D items left for colleague.

## Untracked Files

The following untracked files were created in this pass and committed:

- `components/Application/Cache/System/Configuration/CacheServiceProvider.php`
- `components/Application/Config/System/Configuration/AppConfigurator.php`
- `components/Application/Config/System/Configuration/ConfigFileLoader.php`
- `components/Application/Config/System/Configuration/ConfigLoaderInterface.php`
- `components/Application/Config/System/Configuration/ConfiguratorInterface.php`
- `components/Application/Container/System/Foundation/tools/` (3 files)
- `components/Application/Filesystem/System/Configuration/RegisterFilesystem.php`
- `components/HTTP/Request/System/Capabilities/IncomingRequest/ServerRequest.php`
- `components/Identity/Auth/System/Capabilities/Integrations/Http/` (4 files)
- `examples/Auth/` (11 files)
- `examples/Cache/` (3 files)

## Qoder Worktrees

Not applicable.

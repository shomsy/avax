# V5.8.3 DI Assembly Discipline Remediation

## Findings Fixed

### 1. DataStack/Database/System/Capabilities/Query/Query.php
- **Before**: `private GrammarInterface $grammar = new MySQLGrammar()`, `?? new CreateBuilder()`
- **After**: `GrammarInterface $grammar` and `CreateBuilder $createBuilder` required constructor params
- **Assembly**: DatabaseBuilder now passes MySQLGrammar and CreateBuilder

### 2. DataStack/Database/System/Capabilities/Connections/Connections.php
- **Before**: `?? new ReadPdo()`, `?? new RunWithConnection()`
- **After**: `ReadPdo` and `RunWithConnection` required constructor params
- **Assembly**: DatabaseBuilder now creates and passes both

### 3. DataStack/Database/System/Capabilities/Connections/ReadConnection/ReadConnection.php
- **Before**: `?? new ResolveDefaultConnection()`, `?? new RememberConnection()`
- **After**: Required constructor params, proper parameter order
- **Assembly**: DatabaseBuilder now creates and passes both

### 4. DataStack/Database/System/Capabilities/Telemetry/Telemetry.php
- **Before**: `= new ExecutionScope()` default property
- **After**: `ExecutionScope|null $executionScope = null`

### 5. DataStack/Database/System/Capabilities/Telemetry/Events/EventBus.php
- **Before**: `= new SyncDispatchStrategy()` default property
- **After**: `DispatchStrategyInterface|null $dispatchStrategy = null` with null-safe dispatch

### 6. DataStack/Database/System/Capabilities/Migrations/Migrations.php
- **Before**: `?? new Schema()` in schema() method
- **After**: `Schema $schema` required constructor param

### 7. framework/System/PublicSurface/App.php
- **Before**: `new RenderApplicationError(responseFactory: $this->responseFactory ?? new ResponseFactory())`
- **After**: Uses injected `$this->errorRenderer` or creates default with both ResponseFactory and ClassifyApplicationException

### 8. HTTP/Router/System/Configuration/RouterBuilder.php
- **Before**: `new Router()` with 0 params
- **After**: Properly assembles Router with ResolveCallable, RouteCollection, MatchRoute

## Patterns Still Present (Accepted)

- `new MySQLGrammar()` in DatabaseBuilder — assembly code, allowed
- `new ResolveCallable()` in RouterBuilder — assembly code, allowed
- `new *()` in test setup — allowed
- `new *()` in Flow/Capability constructors for value objects — allowed
- QueryOrchestrator `??= ExecutionScope::fresh()` — value object factory, not infrastructure

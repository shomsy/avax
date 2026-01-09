# Avax Container

Avax Container is a semantic DI engine with a fluent, minimal API and strategy-driven resolution.

## Architecture Overview

The container follows a **Kernel + Pipeline + Steps** architecture for enterprise-grade dependency injection:

### Kernel Architecture
```
Container (PSR-11 Facade)
    “ delegates to
ContainerKernel (Orchestrator)
    “ runs
ResolutionPipeline (Ordered Steps)
    “ executes
Steps: Analyze ’ Guard ’ Resolve ’ Inject ’ Invoke ’ Store ’ Collect
```

### Pipeline Steps
1. **AnalyzePrototypeStep** - Dependency analysis and prototype preparation
2. **GuardPolicyStep** - Security and policy enforcement
3. **ResolveInstanceStep** - Core service instantiation via ResolutionEngine
4. **InjectDependenciesStep** - Property and method injection
5. **InvokePostConstructStep** - Lifecycle hook execution
6. **StoreLifecycleStep** - Scope and lifecycle management
7. **CollectDiagnosticsStep** - Metrics and telemetry collection

This modular design enables clean separation of concerns, comprehensive testing, and enterprise scalability.

## Fluent API Overview

```php
$service = $container->resolve(FooService::class);
$container->inject($service);
```

## Quick Usage

```php
use Avax\Container\Core\Container;

$container = Container::create();
$container->bind(FooService::class, FooService::class);

$service = $container->resolve(FooService::class);
$container->inject($service);
```

## CLI

```bash
php bin/container container:validate --report=json
php bin/container container:health
php bin/container container:report
php bin/container container:hooks
php bin/container container:coverage
```

## Fallback Logger

If no `LoggerInterface` is registered, a stderr fallback logger is provided.
Override it by binding your own logger:

```php
use Psr\Log\LoggerInterface;

$container->bind(LoggerInterface::class, fn () => new MyLogger());
```

## Custom Cache Adapter

Register a custom reflection cache adapter from any service provider:

```php
use Avax\Container\Attributes\Internal\Cache\ReflectionCacheInterface;
use Avax\Container\Application\Provider\ServiceProvider;

final class RedisReflectionCacheProvider extends ServiceProvider
{
    public function register() : void
    {
        $this->registerCacheAdapter('redis', new RedisReflectionCache());
    }

    public function boot() : void {}
}
```

## Runtime Service Graph

Generate a quick dependency graph:

```bash
php bin/container container:graph --format=mermaid
```
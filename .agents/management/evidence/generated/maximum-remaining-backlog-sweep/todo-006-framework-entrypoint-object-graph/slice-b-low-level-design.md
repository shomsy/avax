# TODO-006 Slice B Low-Level Design

## New Class

`Avax\Framework\System\Configuration\BootDsl\BuildBootDslEngine`

Signature:

```php
public static function fromBootOptions(
    string $projectPath,
    string $environmentName,
    Clock|null $clock,
    string $runtimeName,
    Closure|null $httpHandler,
    array $providers,
    array $consoleCommands,
): BootDslEngine
```

The method returns a ready `BootDslEngine`.

## PublicSurface Change

`BootDsl::create()` remains:

```php
public function create(): App
```

It still throws:

```text
Project path is required. Call ->from() before ->create().
```

After validation it calls:

```php
BuildBootDslEngine::fromBootOptions(...)->boot();
```

## Optional Internal Deduplication

`BootDslBuilder::create()` may call `BuildBootDslEngine` as well, because it currently duplicates the same internal graph in Configuration. This is allowed only if the public behavior and tests remain unchanged.

## Contract Test

Update `V4AppDoesNotDuplicateComponentsTest` to prove:

- `BootDsl.php` does not instantiate `BootDslEngine`
- `BuildBootDslEngine.php` owns `new BootDslEngine`

## Failure Semantics

- public missing-project-path failure remains in `BootDsl::create()`
- provider class failures remain in `BootDslEngine::registerProviders()`
- no catch-and-ignore behavior is introduced

## Performance And Runtime

- boot-time allocation is unchanged in amount but moved to Configuration
- no request hot-path behavior changes
- no cache is introduced

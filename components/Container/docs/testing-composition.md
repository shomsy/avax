# Testing Composition

Tests should compose only the slices they actually need.

## Preferred Pattern

Use [
`src/Capabilities/Composition/Testing/TestComposition.php`](../DI/Capabilities/Composition/Testing/TestComposition.php)
when the test wants an ownership-aware helper instead of writing the full setup inline.

Example:

```php
$composition = TestComposition::create();

$composition->singletonCapability(
    'capability.identity',
    PasswordHasher::class,
    BcryptPasswordHasher::class,
    exported: true
);

$composition->bindFlow(
    'flow.login',
    LoginFlow::class,
    LoginFlow::class,
    entry: true,
    imports: ['capability.identity']
);

$composition->override(
    PasswordHasher::class,
    FakePasswordHasher::class,
    'test-double'
);
```

The helper keeps the same authored ownership rules as the real container.

You can still use the real container with explicit ownership metadata directly:

```php
$container->bind(LoginFlow::class, LoginFlow::class)
    ->asFlow('flow.login')
    ->asPrivate()
    ->import('capability.identity');

$container->singleton(PasswordHasher::class, BcryptPasswordHasher::class)
    ->asCapability('capability.identity')
    ->asShared()
    ->export()
    ->overrideSource('test-double');
```

This keeps tests honest about:

- what belongs to the flow under test
- what is imported from shared capabilities
- what was overridden for the test
- what the composed graph looks like right now through `snapshot()`

## Helper Surface

`TestComposition` currently provides:

- `bindFlow()`
- `singletonCapability()`
- `bindCapability()`
- `bindConfiguration()`
- `bindFoundation()`
- `override()`
- `snapshot()`

## Cleanup Rules

- use `reset()` between worker-style test jobs
- use `flush()` when the test also needs compile and derived caches cleared
- use `openScope()` / `closeScope()` for explicit operation/request boundaries

## Debug Rules

Use diagnostics instead of guessing:

- `describeService()` for one unit
- `debugGraph()` for slice and impact shape
- `validate()` for ownership, lifetime, and duplicate concept issues
- `TestComposition::snapshot()` for a test-scoped composition artifact

The test model should stay explicit. There is no hidden test-only global registry.

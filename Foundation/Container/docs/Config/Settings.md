# Settings

## Quick Summary

- Simple key/value configuration store with dot-notation access.
- Used as the primary config store in the new deterministic boot flow; bound as `Settings` and alias `config`.

### For Humans: What This Means

It’s your single source of configuration; no extra config providers needed.

## Terminology

- **Dot notation**: Access nested arrays with strings like `app.debug`.
- **Alias**: Bound both as `Settings` and string `'config'` for convenience.

### For Humans: What This Means

You can read/write settings with `app.debug`, and resolve it via `Settings` or `'config'`.

## Think of It

Like a small dictionary: you can read, write, and check keys with dot paths.

### For Humans: What This Means

It’s a lightweight config bag you can reach from the container.

## Story Example

```php
$settings = $container->get(\\Avax\\Container\\Config\\Settings::class);
$settings->set('app.debug', true);
$debug = $settings->get('app.debug'); // true
```

### For Humans: What This Means

You store and read flags/values easily during boot or runtime.

## For Dummies

1) Use `get('a.b')` to read, `set('a.b', $v)` to write.
2) `has('a.b')` checks existence, `all()` returns the full array.

### For Humans: What This Means

Treat it like an array with dot-path helpers.

## How It Works (Technical)

- Wraps an internal array; `get/set/has/all` support dot-separated keys.
- Bound into the container at boot by `AppFactory` as a singleton and alias `'config'`.

### For Humans: What This Means

It’s simple PHP arrays under the hood; container binding makes it available everywhere.

## Architecture Role

- Lives in `Config/` as the config store.
- Bound by `Core/AppFactory.php`; can be overridden by providers if needed.

### For Humans: What This Means

It’s the default config bag for the new boot flow.

## Methods

- `get(string $key, mixed $default = null): mixed`
- `set(string $key, mixed $value): void`
- `has(string $key): bool`
- `all(): array`

### For Humans: What This Means

Read, write, check, and dump settings.

## Risks & Trade-offs

- No validation; it’s just a bag. Use disciplined keys.
- If another provider rebinds `'config'`, ensure it stays consistent with this store.

### For Humans: What This Means

Keep bindings aligned; don’t mix multiple config stores unless you mean to.

## Related Files & Folders

- `Core/AppFactory.php`: Binds Settings and `'config'` alias.
- `Config/Settings.php`: Implementation.

### For Humans: What This Means

Factory makes it available; this file documents how to use it.

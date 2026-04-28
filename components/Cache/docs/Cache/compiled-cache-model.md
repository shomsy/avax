---
title: compiled-cache-model
owner: CompiledCache Team
last_reviewed: 2026-04-25
classification: internal
---

# Compiled Cache Model

## What Compiled Cache Is

Compiled Cache is a subsystem for **framework-generated PHP artifacts**: routes, config, container, events, middleware,
metadata, templates, translations.

## What Compiled Cache Is NOT

- NOT runtime key/value cache (that's `StoreCachedValues`)
- NOT another `FileCacheStore`
- NOT Redis-based cache
- NOT user-controlled executable generation

## Difference from StoreCachedValues

| Runtime Cache    | Compiled Cache                 |
|------------------|--------------------------------|
| key → value      | artifact name → PHP file       |
| expires by TTL   | stale by source mtime/checksum |
| PSR-16 interface | custom interface               |
| runtime values   | framework artifacts            |

## Supported Artifacts

```php
$compiledCache->read('routes', $routesBuilder, $sources);
$compiledCache->read('config', $configBuilder, $sources);
$compiledCache->read('container', $containerBuilder, $sources);
$compiledCache->read('events', $eventsBuilder, $sources);
```

## How Freshness Works

1. Each artifact has source files
2. Sources have mtime and optional checksum
3. Manifest stores source fingerprint
4. If source changes → rebuild

## How Atomic Writes Work

1. Build PHP payload
2. Write to temporary file (`.tmp.{random}`)
3. Rename temporary → final
4. Update manifest after success

## Clear and Warm

- Clear removes artifact file + manifest entry
- ClearAll removes all artifacts + manifest
- Warm compiles multiple artifacts at startup
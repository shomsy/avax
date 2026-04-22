# Session Framework V4.2 — Concurrency & Recovery Edition

## Highlights

- Redis and File locking support
- Lock metrics collection
- Migration & recovery utilities
- Observability via metrics interface
- Docker-ready health probe

## Example

```php
$lockManager = new RedisSessionLockManager($redis);
$migrator = new SessionMigrator($fileStore, $redisStore);
$probe = new SessionHealthProbe('/var/lib/php/sessions');

if (!$probe->isHealthy()) {
    throw new RuntimeException('Session storage is not healthy');
}
```

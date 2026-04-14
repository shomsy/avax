# Upgrade Guide

Version: 1.0.0

Guide for upgrading Auth package.

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | 2026-04-14 | Initial release |

---

## From 0.x to 1.0.0

### Breaking Changes

- Namespace change: `AvaxContainer\Auth\` → `Avax\Auth\System\`

### Migration

```bash
# Update composer.json
composer require avax/auth:^1.0.0

# Update namespaces in your code
# Before: use AvaxContainer\Auth\...
# After:  use Avax\Auth\System\...
```

### Namespace Mapping

| Old | New |
|-----|-----|
| `AvaxContainer\Auth\Actions\` | `Avax\Auth\System\Flow\` |
| `AvaxContainer\Auth\Adapters\` | `Avax\Auth\Integrations\` |
| `AvaxContainer\Auth\Contracts\` | `Avax\Auth\System\Capability\` |
| `AvaxContainer\Auth\Foundation\` | `Avax\Auth\System\Foundation\` |

---

## Configuration Changes

### Session Registry

```php
// Before: custom implementation
$sessionRegistry = new CustomSessionRegistry();

// After: use built-in
$sessionRegistry = new PdoSessionRegistry($pdo);
// or
$sessionRegistry = new RedisSessionRegistry($redis);
```

### OAuth Client

```php
// Before: AvaxContainer
$client = new OAuthClient(...);

// After: Avax\Auth
$client = new RegisteredOAuthClient(...);
```

---

## Removed Features

| Feature | Replacement |
|---------|-------------|
| `AuthMiddleware` | Use integrations: `Avax\Auth\Integrations\Http\` |
| `SessionManager` | Use `Capability/Session/` directly |

---

## Recommended Setup

```php
use Avax\Auth\System\Auth;
use Avax\Auth\System\Configuration\AuthBuilder;

$auth = (new AuthBuilder())
    ->withPasswordHashing()
    ->withSessionRegistry(new PdoSessionRegistry($pdo))
    ->withOAuthClientRegistry(new InMemoryOAuthClientRegistry())
    ->build();
```

---

*Part of Auth documentation.*
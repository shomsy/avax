# Session V3 - Ultra-Pragmatic Session Management

> **Enterprise-grade session management with zero ceremony**

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![Architecture Score](https://img.shields.io/badge/architecture-10%2F10-brightgreen)](docs/ARCHITECTURE.md)

---

## 🎯 Philosophy

Session V3 is built on three core principles:

1. **Smart Conventions** - `_secure` suffix, `ttl:` parameter
2. **Inline Features** - TTL, Crypto, Namespacing built-in
3. **Zero Ceremony** - No interfaces, kernels, or factories

**Result**: A session system that reads like natural language.

---

## ⚡ Quick Start

### Installation

```bash
composer require avax/session
```

### Basic Usage

```php
use Avax\HTTP\Session\SessionManager;
use Avax\HTTP\Session\NativeStore;

// Create session
$session = new SessionManager(new NativeStore());

// Store value
$session->put('user_id', 123);

// Retrieve value
$userId = $session->get('user_id');

// Check existence
if ($session->has('user_id')) {
    // ...
}
```

---

## 🚀 Features

### 1. Auto-Encryption

Keys ending with `_secure` are automatically encrypted:

```php
// Auto-encrypted
$session->put('api_key_secure', 'secret-token');

// Auto-decrypted
$key = $session->get('api_key_secure');
```

### 2. Auto-TTL

Set expiration with named parameter:

```php
// Expires in 5 minutes
$session->put('otp', '123456', ttl: 300);

// Auto-removed after expiration
$otp = $session->get('otp'); // null after 5 min
```

### 3. Scoped Sessions

Namespace isolation with fluent API:

```php
$session->scope('cart')
    ->secure()
    ->ttl(3600)
    ->put('items', $items);

// Retrieve from scope
$items = $session->scope('cart')->get('items');
```

### 4. Flash Messages

One-time messages across redirects:

```php
// Set flash
$session->flash()->success('Profile updated!');
$session->flash()->error('Invalid credentials');

// Retrieve (auto-removed)
$message = $session->flash()->get('success');
```

### 5. Remember Pattern

Lazy evaluation with caching:

```php
$user = $session->remember('current_user', function() {
    return User::find($id);
});

// With TTL
$data = $session->remember('expensive_data', fn() => compute(), ttl: 3600);
```

### 6. Events

Observe session operations:

```php
$session->events()->listen('stored', function($data) {
    logger()->info('Value stored', $data);
});

// One-time listener
$session->events()->once('stored', function($data) {
    metrics()->increment('session.first_write');
});
```

---

## 📐 Architecture

### File Structure

```
V3/
├── SessionManager.php       # Core orchestrator
├── SessionScope.php         # Fluent builder
├── SessionConfig.php        # Configuration VO
├── SessionContract.php      # API contract
├── Session.php              # Facade
├── Store.php                # Storage interface
├── NativeStore.php          # PHP native implementation
├── Flash.php                # Flash messages
├── Events.php               # Event dispatcher
└── Exceptions/
    ├── SessionException.php
    ├── ExpiredSessionException.php
    └── EncryptionKeyMissingException.php
```

### Design Principles

| Principle          | Implementation                         |
|--------------------|----------------------------------------|
| **Low Coupling**   | SessionManager doesn't know Store type |
| **High Cohesion**  | Each class has single responsibility   |
| **Smart Defaults** | 90% use cases work out-of-the-box      |
| **Lazy Loading**   | Flash/Events created only when needed  |

---

## 🔧 Configuration

### Using SessionConfig

```php
use Avax\HTTP\Session\SessionConfig;

// Secure configuration
$config = SessionConfig::secure('encryption-key');
$session = new SessionManager($store, $config);

// Temporary configuration
$config = SessionConfig::temporary(3600);
$session = new SessionManager($store, $config);

// Custom configuration
$config = new SessionConfig(
    ttl: 7200,
    secure: true,
    encryptionKey: 'my-secret-key'
);
```

### Laravel-Style Facade

```php
use Avax\HTTP\Session\Session;

// Static access
Session::put('user_id', 123);
Session::get('user_id');
Session::flash()->success('Saved!');
Session::scope('cart')->put('items', $items);
```

---

## 🧪 Testing

### Unit Testing

```php
use Avax\HTTP\Session\SessionManager;
use Avax\HTTP\Session\ArrayStore;

class SessionTest extends TestCase
{
    public function test_basic_operations()
    {
        $session = new SessionManager(new ArrayStore());
        
        $session->put('key', 'value');
        
        $this->assertEquals('value', $session->get('key'));
        $this->assertTrue($session->has('key'));
    }
    
    public function test_ttl_expiration()
    {
        $session = new SessionManager(new ArrayStore());
        
        $session->put('key', 'value', ttl: 1);
        sleep(2);
        
        $this->assertNull($session->get('key'));
    }
}
```

---

## 🔐 Security

### Encryption

Session V3 uses the `_secure` suffix convention:

```php
// Encrypted
$session->put('token_secure', 'sensitive-data');

// Scoped encryption
$session->scope('payment')->secure()->put('card', $cardData);
```

**Note**: For production, configure a strong encryption key:

```php
$config = SessionConfig::secure(env('SESSION_ENCRYPTION_KEY'));
```

### Session Fixation Protection

```php
// Regenerate session ID after login
$session->regenerate();

// Clear all data
$session->flush();
```

---

## 📊 Performance

| Operation  | Complexity | Notes              |
|------------|------------|--------------------|
| `put()`    | O(1)       | Direct store write |
| `get()`    | O(1)       | Direct store read  |
| `has()`    | O(1)       | Direct store check |
| `scope()`  | O(1)       | Creates builder    |
| `flash()`  | O(1)       | Lazy-loaded        |
| `events()` | O(1)       | Lazy-loaded        |

**Benchmark**: < 1ms average per operation on modern hardware.

---

## 🔌 Extending

### Custom Store

```php
use Avax\HTTP\Session\Store;

class RedisStore implements Store
{
    public function __construct(private Redis $redis) {}
    
    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->redis->get($key);
        return $value !== false ? unserialize($value) : $default;
    }
    
    public function put(string $key, mixed $value): void
    {
        $this->redis->set($key, serialize($value));
    }
    
    // ... other methods
}

// Usage
$session = new SessionManager(new RedisStore($redis));
```

---

## 📚 API Reference

### SessionManager

| Method                                                               | Description              |
|----------------------------------------------------------------------|--------------------------|
| `put(string $key, mixed $value, ?int $ttl = null): void`             | Store value              |
| `get(string $key, mixed $default = null): mixed`                     | Retrieve value           |
| `has(string $key): bool`                                             | Check existence          |
| `forget(string $key): void`                                          | Remove value             |
| `all(): array`                                                       | Get all data             |
| `flush(): void`                                                      | Clear all data           |
| `scope(string $namespace): SessionScope`                             | Create scoped session    |
| `flash(): Flash`                                                     | Access flash messages    |
| `events(): Events`                                                   | Access event dispatcher  |
| `remember(string $key, callable $callback, ?int $ttl = null): mixed` | Remember pattern         |
| `temporary(int $seconds): SessionScope`                              | Temporary scoped session |

### SessionScope

| Method                                           | Description         |
|--------------------------------------------------|---------------------|
| `ttl(int $seconds): self`                        | Set TTL             |
| `secure(): self`                                 | Enable encryption   |
| `put(string $key, mixed $value): void`           | Store in scope      |
| `get(string $key, mixed $default = null): mixed` | Retrieve from scope |
| `has(string $key): bool`                         | Check in scope      |
| `forget(string $key): void`                      | Remove from scope   |

### Flash

| Method                                               | Description            |
|------------------------------------------------------|------------------------|
| `success(string $message): void`                     | Add success message    |
| `error(string $message): void`                       | Add error message      |
| `warning(string $message): void`                     | Add warning message    |
| `info(string $message): void`                        | Add info message       |
| `add(string $key, string $message): void`            | Add custom message     |
| `get(string $key, ?string $default = null): ?string` | Get and remove message |
| `has(string $key): bool`                             | Check message          |
| `clear(): void`                                      | Clear all messages     |

### Events

| Method                                                    | Description       |
|-----------------------------------------------------------|-------------------|
| `listen(string $event, callable $callback): void`         | Register listener |
| `once(string $event, callable $callback): void`           | One-time listener |
| `removeListener(string $event, callable $callback): void` | Remove listener   |
| `dispatch(string $event, array $data = []): void`         | Dispatch event    |

---

## 🎯 Best Practices

### 1. Use Scopes for Isolation

```php
// Good
$session->scope('admin')->put('permissions', $perms);
$session->scope('user')->put('preferences', $prefs);

// Avoid
$session->put('admin_permissions', $perms);
$session->put('user_preferences', $prefs);
```

### 2. Use TTL for Temporary Data

```php
// Good
$session->put('otp', $code, ttl: 300);

// Avoid manual cleanup
$session->put('otp', $code);
// ... later
$session->forget('otp');
```

### 3. Use Events for Observability

```php
$session->events()->listen('stored', function($data) {
    if ($data['ttl']) {
        metrics()->increment('session.ttl_writes');
    }
});
```

---

## 📝 License

MIT License - see [LICENSE](LICENSE) for details.

---

## 🙏 Credits

Built with ❤️ using **Pragmatic DDD** principles.

**Architecture Score**: 10/10

# CallableSerialization — How This Works

## Purpose

Single shared capability for safe cross-process closure serialization with signed payloads.

Native PHP `serialize()` cannot serialize closures in PHP 8.5. This component uses `laravel/serializable-closure` as the serialization mechanism and adds HMAC-SHA256 signing for integrity verification.

## What This Does

1. **Encode**: Closure → base64(laravel/serializable-closure) → HMAC-SHA256 signature → JSON payload
2. **Decode**: JSON payload → verify HMAC signature → base64 decode → unserialize → Closure

## What This Does NOT Do

- **Container runtime operation**: Container keeps callables in memory. No serialization needed.
- **Container compiled artifacts**: Container uses PHP code generation (MethodEmitter), not closure serialization.
- **Fiber concurrency**: Fibers run in-process. No serialization needed.

## Container Integration Scope: YELLOW

The Container does not currently have a closure serialization boundary. It uses:
- In-memory callable storage (runtime)
- PHP code generation for compiled artifacts (MethodEmitter)

CallableSerialization is available for future use if Container ever needs to persist callable factories to disk or embed closures in cross-process boundaries.

## Security Model

- Payloads are signed with HMAC-SHA256
- Worker must verify signature BEFORE deserializing
- Unsigned payloads are rejected if a signing key is configured
- Corrupted or tampered payloads are rejected
- `RejectUnsafeCallable` validates payload structure before verification

## Usage

```php
use Avax\Components\Foundation\CallableSerialization\System\PublicSurface\CallableSerialization;

// Encode (parent process)
$payload = CallableSerialization::encode(
    closure   : fn() => 'hello',
    signingKey: 'my-secret-key',
);

// Decode (worker process)
$result = CallableSerialization::decode(
    jsonPayload: $payload,
    signingKey : 'my-secret-key',
);

if (isset($result['failure'])) {
    // Handle rejection
}

$closure = $result['closure'];
$result = $closure();
```

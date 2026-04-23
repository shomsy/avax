# Configuration - How This Works

## Purpose

Session configuration value objects and assembly.

## Configs

- `SessionConfig` - Main session config
- `SessionCookieConfig` - Cookie settings
- `SessionSecurityConfig` - Security settings
- `SessionRegistryConfig` - Registry settings
- `SessionRecoveryConfig` - Recovery settings
- `BuildSession` - Session builder

## Example

```php
$config = new SessionConfig(
    name: 'SID',
    lifetime: 3600,
    secure: true,
    httpOnly: true,
    encrypt: true,
    encryptionKey: 'key32bytes!!!'
);

$session = (new BuildSession($config))->build($store);
```
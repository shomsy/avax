# PHP Auth Framework — Language-Specific Rules

This folder contains Auth framework-specific rules that supplement the reusable
PHP profile in `.agents/.rules/governance/profiles/languages/php.md`.

## Framework Version Rules

- **PHP Minimum**: 8.3
- **PHP Target**: Latest stable (move to new versions within 6 months of release)
- **Strict Types**: Always `declare(strict_types=1)`

## Auth Framework Architecture

### Feature-Sliced Structure

```
Foundation/Auth/
├── Login/           # Login feature slice
│   ├── LoginAction.php
│   ├── LoginController.php
│   └── LoginRequest.php
├── Register/        # Registration feature slice
├── Session/         # Session management
├── Access/          # Authorization & permissions
└── Shared/          # Shared contracts, exceptions, DTOs
```

### File Naming

- Use PascalCase for class files: `LoginAction.php`
- Use PascalCase for directories: `Login/`, `Session/`
- One class per file
- File name must match class name

### Method Naming (DSL Style)

```php
// Good — clear, action-oriented
$auth->login($credentials);
$auth->logout();
$auth->user();
$auth->check();

// Bad — verbose, implementation-focused
$auth->authenticateUser($credentials);
$auth->terminateUserSession();
$auth->getCurrentAuthenticatedUser();
$auth->verifyAuthenticationStatus();
```

## Core Classes

### Authenticator (Facade)

Entry point for all auth operations. Delegates to actions.

```php
final readonly class Authenticator implements AuthInterface
{
    public function __construct(
        private Login $loginAction,
        private Logout $logoutAction,
        private GetUser $getUserAction,
        private Check $checkAction
    ) {}
}
```

### Actions (Use Cases)

One action per business operation. Thin, delegating to adapters.

```php
final readonly class Login
{
    public function __construct(private IdentityInterface $identity) {}

    public function execute(Credentials $credentials): UserInterface
    {
        if (! $this->identity->attempt(credentials: $credentials)) {
            throw new AuthFailed(message: 'Invalid credentials.');
        }

        return $this->identity->user();
    }
}
```

### Adapters (Implementations)

Pluggable implementations for:
- **Identity** — User retrieval and authentication
- **Session** — Session storage (JWT, server-side)
- **AccessControl** — Role/permission checking
- **RateLimiter** — Request throttling

### Contracts (Interfaces)

Define boundaries. All external dependencies through interfaces.

## Security Requirements

### Sensitive Data

```php
use SensitiveParameter;

public function login(#[SensitiveParameter] Credentials $credentials): UserInterface
{
    // $credentials->getPassword() never appears in logs
}
```

### Password Handling

- Use `password_hash()` / `password_verify()`
- Default to bcrypt (cost 12)
- Support argon2id for higher security
- Never log password attempts

### Session Security

- HttpOnly cookies by default
- Secure (HTTPS-only) in production
- SameSite=strict or lax
- Regenerate ID on login

### Rate Limiting

- Track by IP + identifier
- Exponential backoff on failures
- Clear feedback (429 status)

## Error Handling

### Authentication Failures

```php
// Generic message — don't reveal if email exists
throw new AuthFailed(message: 'Invalid credentials.');
```

### Authorization Failures

```php
// Forbidden — user authenticated but not permitted
throw new Forbidden(message: 'Access denied.');
```

### Exception Hierarchy

```
AuthException (base)
├── AuthFailed (login failure)
├── Forbidden (authorization failure)
└── RateLimitExceeded (too many attempts)
```

## Testing Requirements

### Unit Tests

- Test each action in isolation
- Mock adapters
- Cover success and failure paths

### Integration Tests

- Test with real identity adapter
- Test session handling
- Test middleware flow

### Security Tests

- Password hashing verification
- Session hijacking prevention
- Rate limit behavior

## Composer Configuration

```json
{
    "name": "avax/auth",
    "description": "Pure PHP 8.3+ authentication/authorization framework",
    "type": "library",
    "require": {
        "php": "^8.3"
    },
    "require-dev": {
        "phpunit/phpunit": "^11.0",
        "phpstan/phpstan": "^1.10"
    },
    "autoload": {
        "psr-4": {
            "Avax\\Auth\\": "src/"
        }
    }
}
```

## Development Commands

```bash
# Install dependencies
composer install

# Run tests
composer test

# Static analysis
composer analyse

# Format code
composer format
```
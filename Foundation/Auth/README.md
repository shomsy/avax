# Avax Auth

> Pure PHP 8.3+ authentication and authorization framework.

A **feature-sliced**, **security-first**, **DSL-driven** authentication framework for PHP applications. Prolifically designed for **absurd simplicity on the surface** and **enterprise-grade integrity underneath**.

## Features

- **Fluent Configuration DSL** - Natural, predictable setup.
- **Unified Identity Management** - Session and JWT support in one place.
- **Granular Access Control** - Enforceable RBAC (Roles and Permissions).
- **Brute Force Protection** - Integrated protection flow.
- **Secure Password Hashing** - Bcrypt (cost 12+) by default.
- **Strictly Typed** - Built with PHP 8.3+ and Value Objects.

## Installation

```bash
composer require avax/auth
```

## Quick Start

### Configuration

```php
use Avax\Auth\System\Auth;

$auth = Auth::configuration()
    ->forUser($userSource)
    ->withSession($sessionIdentity)
    ->protectFromBruteForce($protection)
    ->ready();
```

### Authentication Flow (Flows)

```php
use Avax\Auth\System\Flows\Login\Credentials;
use Avax\Auth\System\Flows\Register\RegistrationData;

// Login
$user = $auth->login(new Credentials(
    identifier: 'user@example.com',
    password: 'secret_password_123'
));

// Registration
$auth->register(new RegistrationData(
    email: 'new@example.com',
    username: 'new_user',
    password: 'secure_password'
));

// Status & Logout
if ($auth->check()) {
    $currentUser = $auth->user();
}

$auth->logout();
```

### Authorization (Access)

```php
use Avax\Auth\System\Capabilities\User\UserPermission;
use Avax\Auth\System\Capabilities\User\UserRole;

$auth->access()->requireRole(UserRole::ADMIN);
$auth->access()->requirePermission(new UserPermission('delete_user'));
```

## Architecture

This framework uses a **feature-first** architecture where each business flow lives in its own owner-centric folder:

```
System/
├── Auth.php
├── AuthInterface.php
├── Configuration/     # Composition Root & Fluent Builder
├── Flows/             # Verb-Noun Business Actions (Login, Register, etc.)
├── Capabilities/
│   ├── Access/        # Root authorization façade plus specialized checks
│   ├── Identity/      # Unified authentication façade plus adapters
│   ├── PasswordHashing/
│   ├── User/          # Core Domain Data & Value Objects
│   └── UserSource/    # User Data Storage Port
└── Foundation/        # Core Primitives
```

## Documentation

- [Architecture Overview](./docs/architecture.md)
- [Security Rules & Best Practices](./docs/security-rules.md)
- [Authorization Flow](./docs/authorization-flow.md)
- [JWT Identity Guide](./docs/jwt-flow.md)
- [Session Identity Guide](./docs/session-flow.md)

## Requirements

- PHP 8.3 or higher
- `ext-pdo` (for database sources)
- `firebase/php-jwt` (for JWT flows)

## License

MIT

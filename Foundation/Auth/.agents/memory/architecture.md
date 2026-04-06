# Auth Framework — Architecture Decisions

This file tracks architectural decisions and design patterns used in the Auth framework.

## Architecture Pattern

**Feature-Sliced Vertical Architecture**

- Root folder: Business flow or capability
- File: Single responsibility
- Function: Exact action

## Structural Decisions

### Current Structure (Layered - needs refactoring)

```
Foundation/Auth/
├── Actions/      # Use cases
├── Adapters/     # Implementations
├── Contracts/   # Interfaces
├── Data/         # DTOs
├── Http/         # Controllers & Middleware
└── Exceptions/
```

### Target Structure (Feature-Sliced)

```
Foundation/Auth/
├── Login/
│   ├── LoginAction.php
│   └── LoginController.php
├── Register/
├── Session/
├── Access/
└── Shared/
    ├── Contracts/
    ├── Adapters/
    └── Exceptions/
```

## Design Patterns Used

### Facade Pattern

`Authenticator` delegates to actions:

```php
final readonly class Authenticator
{
    public function __construct(
        private Login $loginAction,
        private Logout $logoutAction,
        // ...
    ) {}
}
```

### Action/Use Case Pattern

Each business operation is an action:

```php
final readonly class Login
{
    public function execute(Credentials $credentials): UserInterface
}
```

### Adapter Pattern

Pluggable implementations:

```php
class Identity implements IdentityInterface {}
class JwtIdentity extends Identity {}
class SessionIdentity extends Identity {}
```

## Dependency Flow

```
Controller/Entry
    ↓
Action (Use Case)
    ↓
Adapter (Implementation)
    ↓
External (DB, Session, etc.)
```

## Naming Conventions

| Type | Convention | Example |
|------|------------|---------|
| Actions | Verb + Action | `LoginAction.php` |
| Adapters | Noun | `Identity.php` |
| Controllers | Noun + Controller | `LoginController.php` |
| DTOs | Noun + Request/Response/DTO | `Credentials.php` |

## API Design (DSL Style)

```php
$auth->login($credentials);
$auth->logout();
$auth->user();
$auth->check();

$access->can('edit', $resource);
$access->hasRole('admin');
```

## Security Architecture

1. **Credential Protection**: `#[SensitiveParameter]`
2. **Generic Errors**: No user enumeration
3. **Rate Limiting**: Brute-force prevention
4. **Secure Sessions**: HttpOnly, Secure, SameSite
5. **Password Hashing**: bcrypt/argon2

## Testing Strategy

- Unit: Test actions in isolation
- Integration: Test full flows
- Security: Test edge cases

## Decision Log

| Date | Decision | Rationale |
|------|----------|-----------|
| 2026-04-06 | Use feature-sliced | Follows coding standards preference |
| 2026-04-06 | Pure PHP | No framework lock-in |
| 2026-04-06 | DSL naming | Readable, predictable APIs |
# Auth Framework — Coding Standards

> **Purpose**
>
> This document defines coding standards specifically for the **Avax Auth** framework — a pure PHP 8.3+ authentication/authorization system.

---

## 1. Core Philosophy

- **Security-first**: Every decision must consider security implications
- **DSL-driven**: APIs must read like clear sentences
- **Feature-sliced**: Organize by business flow, not technical type
- **No magic**: Explicit is better than implicit

---

## 2. PHP 8.3+ Requirements

- `declare(strict_types=1)` on all files
- Constructor promotion for dependencies
- Readonly properties for immutable state
- Named arguments for clarity
- `Type|null` instead of `?Type`
- Space before return type: `function example() : string`

---

## 3. Security Requirements

### Sensitive Data Handling

```php
use SensitiveParameter;

public function login(
    #[SensitiveParameter]
    Credentials $credentials
) : UserInterface {
    // Password never in logs or stack traces
}
```

### Password Handling

```php
// Always hash with adequate cost
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// Always verify
if (! password_verify($password, $hash)) {
    throw new AuthFailed(message: 'Invalid credentials.');
}
```

### Generic Errors

```php
// DO: Don't reveal if user exists
throw new AuthFailed(message: 'Invalid credentials.');

// DON'T: User enumeration
if (! $user) {
    throw new AuthFailed(message: 'User not found.');
}
```

---

## 4. Architecture Pattern

### Folder Structure (Feature-Sliced)

```
Auth/
├── Login/
│   ├── LoginAction.php
│   ├── LoginController.php
│   └── LoginResponse.php
├── Register/
├── Session/
├── Access/
└── Shared/
    ├── Contracts/
    ├── Adapters/
    ├── Exceptions/
    └── Data/
```

### Dependency Flow

```
Entry (Controller/Facade)
    ↓
Action (Use Case)
    ↓
Contract (Interface)
    ↓
Adapter (Implementation)
    ↓
External (DB, Session, Cache)
```

---

## 5. Naming Conventions

### DSL-Style Method Names

```php
// Good — clear, action-oriented
$auth->login(credentials: $credentials);
$auth->logout();
$auth->user();
$auth->check();

$access->can(action: 'edit', resource: $post);
$access->hasRole(role: 'admin');

// Avoid — verbose, implementation-focused
$auth->authenticateUser(credentials: $credentials);
$auth->terminateCurrentSession();
```

### File and Class Names

| Type | Convention | Example |
|------|------------|---------|
| Action | Verb + Action | `LoginAction.php` |
| Adapter | Noun | `Identity.php` |
| Controller | Noun + Controller | `LoginController.php` |
| DTO | Noun + Request/Response | `LoginRequest.php` |
| Contract | Noun + Interface | `IdentityInterface.php` |
| Exception | Noun + Exception | `AuthFailed.php` |

---

## 6. Code Structure

### Action Pattern

```php
final readonly class Login
{
    public function __construct(
        private IdentityInterface $identity
    ) {}

    /**
     * @throws AuthFailed
     */
    public function execute(
        #[SensitiveParameter]
        Credentials $credentials
    ) : UserInterface {
        if (! $this->identity->attempt(credentials: $credentials)) {
            throw new AuthFailed(message: 'Invalid credentials.');
        }

        $user = $this->identity->user();

        if ($user === null) {
            throw new AuthFailed(message: 'Authentication passed but user retrieval failed.');
        }

        return $user;
    }
}
```

### Facade Pattern

```php
final readonly class Authenticator implements AuthInterface
{
    public function __construct(
        private Login $loginAction,
        private Logout $logoutAction,
        private GetUser $getUserAction,
        private Check $checkAction,
    ) {}

    public function login(Credentials $credentials) : UserInterface
    {
        return $this->loginAction->execute(credentials: $credentials);
    }
}
```

### Adapter Pattern

```php
abstract class Identity
{
    public function __construct(
        protected UserSourceInterface $user
    ) {}

    protected function authenticate(
        #[SensitiveParameter]
        CredentialsInterface $credentials
    ) : UserInterface|null {
        $user = $this->user->retrieveByCredentials(credentials: $credentials);

        if ($user instanceof UserInterface
            && password_verify(
                password: $credentials->getPassword(),
                hash: $user->getPassword()
            )
        ) {
            return $user;
        }

        return null;
    }
}
```

---

## 7. Docblocks

### Required Elements

- One-line intent above class/property
- `@throws` tags for all exceptions
- Parameter types and purposes

```php
/**
 * Authenticates users based on credentials.
 *
 * @param Credentials $credentials User login credentials.
 * @return UserInterface The authenticated user.
 * @throws AuthFailed If authentication fails.
 */
public function execute(Credentials $credentials) : UserInterface
```

### What to Remove

- Comments above `namespace`, `use`
- Obvious comments ("this is a class")
- Auto-generated noise

---

## 8. Testing Requirements

### Unit Tests

- Test actions in isolation
- Mock adapters
- Cover success AND failure paths

### Security Tests

- Verify `#[SensitiveParameter]` prevents logging
- Test password hashing verification
- Test session hijacking prevention
- Test rate limit behavior

---

## 9. Security Invariants

| Invariant | Enforcement |
|-----------|--------------|
| Passwords never logged | `#[SensitiveParameter]` |
| Passwords hashed | `password_hash()` only |
| No user enumeration | Generic errors |
| Secure sessions | HttpOnly, Secure, SameSite |
| Rate limited | Per-attempt tracking |
| Auth failures logged safely | No credentials in logs |

---

## 10. Prohibited Patterns

```php
// NEVER: Store plain text passwords
$user->password = $password;

// NEVER: Log credentials
logger->info("Login attempt", ['email' => $email, 'password' => $password]);

// NEVER: Reveal if user exists
throw new AuthFailed(message: "User {$email} not found");

// NEVER: Allow privilege escalation
if ($user->isAdmin()) { $role = 'admin'; } // Don't grant based on user input

// NEVER: Use weak hashing
$hash = md5($password); // NEVER
```
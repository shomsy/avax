# Auth Framework — Learnings

This file accumulates technical lessons and insights from working on the Auth framework.

## PHP 8.3+ Idioms Learned

### Constructor Promotion

```php
// Good
final readonly class Login
{
    public function __construct(private IdentityInterface $identity) {}
}

// Avoid
class Login
{
    private IdentityInterface $identity;
    
    public function __construct(IdentityInterface $identity) {
        $this->identity = $identity;
    }
}
```

### Readonly Properties

```php
// Good - immutable objects
final readonly class Credentials
{
    public function __construct(
        public string $email,
        #[SensitiveParameter]
        public string $password,
    ) {}
}
```

### Named Arguments

```php
// Good - clear, self-documenting
$auth->login(credentials: $credentials);
$user->getById(id: $userId);

// Avoid - positional, unclear
$auth->login($credentials);
$user->getById($userId);
```

### SensitiveParameter Attribute

```php
use SensitiveParameter;

public function login(
    #[SensitiveParameter]
    Credentials $credentials
) : UserInterface {
    // Password never appears in stack traces or logs
}
```

## Security Patterns

### Generic Auth Failures

```php
// Good - don't reveal if user exists
if (! $identity->attempt(credentials: $credentials)) {
    throw new AuthFailed(message: 'Invalid credentials.');
}

// Bad - user enumeration
if (! $user) {
    throw new AuthFailed(message: 'User not found.');
}
throw new AuthFailed(message: 'Invalid password.');
```

### Password Hashing

```php
// Always hash
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// Always verify
if (! password_verify($password, $hash)) {
    throw new AuthFailed(message: 'Invalid credentials.');
}
```

## Architecture Patterns

### Action Pattern

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
    
    public function login(Credentials $credentials): UserInterface
    {
        return $this->loginAction->execute(credentials: $credentials);
    }
}
```

## Code Review Insights

1. **Always use strict_types**: `declare(strict_types=1);`
2. **Return types with space**: `public function example() : string`
3. **One class per file**: Never multiple classes
4. **Meaningful docblocks**: Explain what, not how
5. **@throws tags**: Document failure modes

## Testing Approaches

- Mock adapters, test actions in isolation
- Test success AND failure paths
- Verify sensitive data never appears in logs

## Update This File

Add learnings here when you discover something worth remembering.
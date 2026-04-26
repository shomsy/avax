# Auth Framework — Documentation Standards

> **Purpose**
>
> This document defines how to document the **Avax Auth** framework.

---

## 1. Class Documentation

### Required Sections

1. **One-line summary** — What the class does
2. **Purpose** — Why it exists, when to use
3. **Public API** — Main methods and their purpose
4. **Dependencies** — What it needs to work

### Template

```php
/**
 * Primary entry point for authentication operations.
 *
 * This facade delegates to action classes (Login, Logout, etc.)
 * and provides a simple API for application integration.
 *
 * ## Usage
 *
 * ```php
 * $user = $auth->login(credentials: new Credentials(
 *     email: 'user@example.com',
 *     password: 'secret'
 * ));
 * ```

*
* ## Dependencies
*
*
    - Login action
*
    - Logout action
*
    - GetUser action
*
    - Check action
      */
      final readonly class Authenticator implements AuthInterface

```

---

## 2. Method Documentation

### Template

```php
/**
 * Authenticates a user with the provided credentials.
 *
 * @param Credentials $credentials The user's email and password.
 * @return UserInterface The authenticated user.
 * @throws AuthFailed If credentials are invalid.
 * @throws RateLimitExceeded If too many attempts.
 */
public function login(Credentials $credentials) : UserInterface
```

### Guidelines

- Start with verb (Authenticates, Retrieves, Checks)
- Document all `@throws` cases
- Include `@param` with types
- Show usage example for complex methods

---

## 3. Security Documentation

### Password Handling

```php
/**
 * Uses bcrypt with cost 12 for hashing.
 * Supports argon2id for higher security needs.
 *
 * ## Security Notes
 *
 * - Passwords are never logged (see #[SensitiveParameter])
 * - Authentication failures use generic messages
 * - Rate limiting prevents brute-force attacks
 */
```

### Error Messages

```php
/**
 * Auth failure message is intentionally generic.
 * This prevents user enumeration attacks.
 *
 * DO: "Invalid credentials."
 * DON'T: "User not found" or "Wrong password"
 */
```

---

## 4. Architecture Documentation

### Folder Structure

```
Auth/
├── Login/           # Login feature
├── Register/        # Registration feature
├── Session/         # Session management
├── Access/          # Authorization & permissions
└── Shared/          # Shared components
```

For each folder, document:

- What business capability it provides
- What files are inside
- How it connects to other features

---

## 5. API Documentation

### Usage Examples

```php
// Basic login
$user = $auth->login(credentials: $credentials);

// Check auth status
if ($auth->check()) {
    $user = $auth->user();
}

// Logout
$auth->logout();

// Authorization check
if ($access->can(action: 'edit', resource: $post)) {
    // Allow
}
```

---

## 6. Security Considerations

Always document security-relevant behavior:

| Topic             | What to Document            |
|-------------------|-----------------------------|
| Password handling | Hash algorithm, cost factor |
| Session security  | Cookie settings, expiry     |
| Rate limiting     | Limits, lockout duration    |
| Error messages    | Why they are generic        |
| Logging           | What is NOT logged          |

---

## 7. Migration Guide

If breaking changes occur, document:

1. What changed
2. How to migrate
3. Why it changed

```php
/**
 * ## Migration from v1.x
 *
 * v2.0 renamed `Auth::authenticate()` to `Auth::login()`.
 *
 * Before:
 * ```php
 * $auth->authenticate($credentials);
 * ```

*
* After:
* ```php
* $auth->login(credentials: $credentials);
* ```

*/

```
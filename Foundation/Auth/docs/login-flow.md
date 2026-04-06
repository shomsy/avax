# Login Flow

## Overview

The login flow authenticates users using credentials (email/username + password).

## Flow Diagram

```
Login Request
    ↓
Check Rate Limit
    ↓
Validate Credentials
    ↓
Read User from Database
    ↓
Verify Password Hash
    ↓
Set Session / Issue JWT
    ↓
Return User
```

## Components

### Login.php (Main Action)
Orchestrates the entire login flow:
1. Check rate limit
2. Attempt authentication
3. Record failed attempt if needed
4. Reset rate limit on success

### Credentials (Value Object)
- `identifier` - Email or username
- `password` - Protected with `#[SensitiveParameter]`

### Identity Action
- Reads user by credentials
- Verifies password hash
- Starts authenticated session

### Rate Limiting
- Prevents brute-force attacks
- Configurable max attempts
- Lockout duration

## Usage

```php
$auth = Auth::createSession(
    userSource: $userSource,
    sessionIdentity: $session
);

$user = $auth->login(new Credentials(
    identifier: 'user@example.com',
    password: 'secret'
));
```

## Security Notes

- Passwords never logged (SensitiveParameter)
- Generic error messages
- Rate limiting enforced
- Session expiry handling
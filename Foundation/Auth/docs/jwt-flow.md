# JWT Authentication Flow

This document describes the stateless JWT-based authentication flow in the Avax Auth framework.

## Overview

Unlike session-based auth, JWT authentication is stateless. The client passes a token in the `Authorization: Bearer <token>` header with every request.

```mermaid
sequenceDiagram
    participant Client
    participant Identity as Identity (Facade)
    participant Auth as Auth (Facade)
    participant Login as Login (Action)
    participant JWT as JwtIdentity (Adapter)
    participant DB as UserSource

    Client->>Auth: login(Credentials)
    Auth->>Login: execute(Credentials)
    Login->>DB: findByIdentifier(email)
    Login->>JWT: issue(User)
    JWT-->>Login: token
    Login-->>Auth: User
    Auth-->>Client: User
    Client->>Identity: token()
    Identity-->>Client: token

    Note over Client, JWT: Subsequent Request
    Client->>Identity: authenticate(token)
    Identity->>JWT: authenticate(token)
    JWT->>DB: validate(token)
    JWT-->>Identity: currentUser
    Client->>Auth: check()
    Auth->>Identity: check()
    Identity-->>Auth: bool
    Auth-->>Client: OK
```

## Setup

To use JWT authentication, initialize the `Auth` instance with a JWT-backed `Identity` façade:

```php
use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentity;

$identity = new Identity(jwtIdentity: new JwtIdentity(
    userSource: $userSource,
    secret: 'your-256-bit-secret'
));

$auth = Auth::configuration()
    ->forUser($userSource)
    ->withIdentity($identity)
    ->ready();
```

## Issuing Tokens

When a user logs in successfully, the `Identity` façade delegates to `JwtIdentity::issue(User)`.
The resulting token is available through the same `Identity` instance via `token()`.

## Validating Tokens

The `JwtIdentity` adapter is responsible for:
1. Receiving a bearer token from the application boundary.
2. Validating the signature.
3. Checking for expiration.
4. Hydrating the current `User` when the token is valid.

The application should pass the bearer token into `Identity::authenticate($token)` before calling `check()` or `user()`.

## Implementation Details

- **Algorithm**: Default is HMAC with SHA-256 (HS256).
- **Stateless**: No server-side session lookup is required, improving scalability.
- **Portability**: Tokens are self-contained and carry user data (claims).

## Security Considerations

- Always use HTTPS to prevent token theft in transit.
- Use a strong, long secret for signing.
- Keep the expiry time short (e.g., 15-60 minutes).
- Consider implementing token blacklisting for revocation if necessary.

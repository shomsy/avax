# Security Rules and Best Practices

This document outlines the security principles and mandatory rules for the Avax Auth framework (v2.0+).

## Core Principles

All components in the **`Foundation/Auth`** must adhere to these non-negotiable security rules:

1.  **Password Hashing**: NEVER store passwords in plain text. Use **`PasswordHasher`** (bcrypt with cost 12 or Argon2ID).
2.  **Generic Error Messages**: On login failure, always return a generic "Invalid credentials" message to prevent user enumeration.
3.  **Sensitive Parameters**: Use the **`#[SensitiveParameter]`** attribute on password and token fields to prevent them from appearing in crash logs or stack traces.
4.  **Brute Force Protection**: Protect authentication endpoints (Login, Register, Password Change) with **`LoginRateLimit`** to mitigate brute-force and credential stuffing.
5.  **No Logic in Entities**: The **`User`** entity must remain a simple, **immutable** data container with no business or database logic.
6.  **Least Privilege**: Always check the minimum required permission (**`RequirePermission`**) before allowing access.
7.  **Session Fixation Prevention**: Always regenerate the session ID on successful login and logout.
8.  **Stateless JWT**: JWTs should be signed and contain only the minimal necessary claims. Avoid putting sensitive data in the token payload.

## Implemented Safeties

- **`PasswordHasher`**: Enforces a minimum cost for hashing and uses PHP's `password_hash()` and `password_verify()`.
- **`LoginRateLimit`**: Tracks failed attempts by identifier (email/username) and blocks further attempts after a threshold.
- **`RequireAuthentication`**: A fail-fast boundary in the **`Access/`** layer that prevents unauthenticated requests from reaching business logic.
- **`RequireRole` / `RequirePermission`**: Fine-grained access control using an RBAC model.

## Implementation Guidelines

### When Creating New Features

- **Check Input**: Always validate input data using a dedicated **`Validate*`** step action.
- **Throw Early**: Throw specific exceptions (**`Unauthenticated`**, **`RoleDenied`**, **`PermissionDenied`**) early in the execution chain.
- **Keep it Feature-First**: Keep security-related logic within the corresponding feature folder.

---
*Security is a foundation, not a feature.*

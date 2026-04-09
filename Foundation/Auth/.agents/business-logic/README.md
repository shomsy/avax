# Auth Framework — Domain Model

## Vision

A pure PHP 8.3+ authentication and authorization framework that provides secure, flexible, and framework-agnostic
identity management for any PHP application.

## Core Value Proposition

- **Security-first**: OWASP-compliant, secure-by-default authentication
- **Framework-agnostic**: Works with any PHP project (Laravel, Symfony, plain PHP, etc.)
- **Developer experience**: Simple, intuitive DSL, feature-sliced architecture
- **Extensible**: Easy to customize identity providers, password hashing, sessions

## User Goals

### For Application Developers

1. **Quick integration** — Add auth to any PHP project in minutes
2. **Secure by default** — Don't think about security; it's built-in
3. **Flexible customization** — Swap components without rewriting core
4. **Clear contracts** — Predictable interfaces, no magic

### For End Users (Application End Users)

1. **Safe login** — Credentials are never exposed or logged
2. **Session management** — Transparent session handling with secure defaults
3. **Permission awareness** — Clear what they can and cannot access
4. **Account recovery** — Secure password reset flow

## Domain Boundaries

### Authentication (Auth)

- **Identity verification**: Credential validation, password verification
- **Session management**: Login, logout, session persistence
- **User retrieval**: Get current user, check auth status
- **Rate limiting**: Prevent brute-force attacks

### Authorization (Access Control)

- **Role-based access control (RBAC)**: Role assignment, role hierarchy
- **Permission management**: Fine-grained permissions
- **Middleware**: Route/endpoint protection
- **Access policies**: Custom access rules

### Security Layer

- **Password hashing**: Secure hash algorithms (bcrypt, argon2)
- **Token management**: JWT, session tokens
- **Rate limiting**: Request throttling
- **Audit logging**: Track auth events

## Key Domain Concepts

| Concept           | Definition                                              |
|-------------------|---------------------------------------------------------|
| **Identity**      | User entity from the data source (database, LDAP, etc.) |
| **Credentials**   | Email/password, API key, or other auth factors          |
| **Session**       | Authenticated state, may be JWT or server-side          |
| **Role**          | Named group of permissions (admin, user, moderator)     |
| **Permission**    | Single action or resource access right                  |
| **Access Policy** | Custom rule that determines if access is granted        |

## Security Invariants

1. **Passwords are never logged or exposed** — Use `#[SensitiveParameter]`
2. **Passwords are hashed** — Never store plain text
3. **Sessions are secure** — HttpOnly, Secure, SameSite cookies
4. **Rate limits enforced** — Prevent brute-force and DoS
5. **Auth failures are generic** — Don't reveal if email exists
6. **Tokens are time-limited** — JWT exp, session timeout

## User-Facing Behaviors

| Behavior                  | Expected Outcome                                                |
|---------------------------|-----------------------------------------------------------------|
| Successful login          | User authenticated, session created, redirect to protected area |
| Failed login              | Error message, rate limit check, no user existence leak         |
| Logout                    | Session destroyed, redirect to public area                      |
| Access protected resource | Success if authorized, 403 if not, 401 if not authenticated     |
| Password reset            | Email with reset token, secure flow                             |

## Questions Each Feature Must Answer

1. What does the user or developer achieve?
2. What does the software do to make that reliable?
3. What is visible at the boundary?
4. What should they understand, trust, or be able to do afterwards?
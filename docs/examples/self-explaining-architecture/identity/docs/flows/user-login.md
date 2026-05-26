# User Login Flow

> Describes the complete user login flow from credential submission to authenticated session.

## Trigger

User submits login credentials (email + password) via the login API endpoint.

## Steps

1. **Receive credentials:** Login API endpoint receives email and password
2. **Validate input:** Validate that email is valid format and password meets minimum requirements
3. **Authenticate:** Call Authenticate capability with email and password
4. **Load user profile:** On success, load user profile for the authenticated user
5. **Create session:** Call Session capability to create a new session
6. **Issue tokens:** Call IssueToken capability to generate access + refresh tokens
7. **Return response:** Return user data, session ID, and tokens

## Participants

| Participant | Responsibility |
|-------------|---------------|
| Login flow | Orchestrates the entire login process |
| Authenticate | Verifies credentials |
| Session Manager | Creates and manages session |
| IssueToken | Generates access and refresh tokens |

## Failure Modes

| Step | Failure | Response |
|------|---------|----------|
| 3 | Invalid credentials | 401, generic "invalid credentials" (no user enumeration) |
| 3 | Account locked | 401, "account temporarily locked" |
| 4 | Profile not found | 500, logged for investigation |
| 5 | Session creation fails | 500, authentication rolled back |

## Security Boundaries

- Authentication happens before session or token creation
- No user enumeration in error messages
- Rate limiting on login attempts
- Account lockout after N failed attempts

## Related Tests

- `tests/Feature/Identity/LoginTest.php`
- `tests/Unit/Identity/Capabilities/AuthenticateTest.php`
- `tests/Unit/Identity/Capabilities/IssueTokenTest.php`

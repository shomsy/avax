# Skill: Security Review

This skill defines how to perform security-focused code review for the Auth framework.

## When to Use

- Reviewing authentication code
- Reviewing authorization code
- Reviewing password handling
- Reviewing session management
- Pre-release security check

## Review Checklist

### Authentication

- [ ] Credentials use `#[SensitiveParameter]`
- [ ] Passwords are hashed, never logged
- [ ] Auth failures are generic (no user enumeration)
- [ ] Rate limiting is in place
- [ ] No timing attacks on login

### Authorization

- [ ] Roles and permissions are properly validated
- [ ] Middleware checks are enforced
- [ ] No privilege escalation possible
- [ ] Access denied returns 403, not 404

### Session Management

- [ ] Session IDs are random and secure
- [ ] Sessions expire appropriately
- [ ] Logout destroys session
- [ ] Cookies are HttpOnly, Secure, SameSite

### Password Handling

- [ ] Uses `password_hash()` with adequate cost
- [ ] No custom hashing algorithms
- [ ] Password reset tokens are random and time-limited
- [ ] No password in URLs or logs

### Data Protection

- [ ] No sensitive data in logs
- [ ] Exceptions don't leak stack traces in production
- [ ] CSRF protection where applicable
- [ ] Input validation on all entry points

## Review Process

1. Read the code without modifications
2. Check against checklist
3. Verify with static analysis
4. Document findings in `.agents/review/REVIEWS.md`

## Severity Levels

| Level        | Description                |
|--------------|----------------------------|
| **Critical** | Immediate security risk    |
| **High**     | Significant vulnerability  |
| **Medium**   | Moderate risk              |
| **Low**      | Minor issue or improvement |
| **Info**     | Observation                |

## Output Format

```
## Security Review: [Feature]

### Findings

1. **Severity**: [Level]
   **Location**: [file:line]
   **Description**: [what was found]
   **Recommendation**: [how to fix]
```
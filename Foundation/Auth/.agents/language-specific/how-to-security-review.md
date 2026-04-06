# Auth Framework — Security Review

> **Purpose**
>
> This document defines a security review process specifically for the **Avax Auth** framework.

---

## 1. Review Scope

### Authentication Security
- Credential handling
- Password storage and verification
- Session management
- Rate limiting

### Authorization Security
- Role-based access control
- Permission validation
- Middleware enforcement

### Data Protection
- Sensitive data in logs
- Error message safety
- Token security

---

## 2. Hard Security Gates

Stop immediately if:
- Passwords stored in plain text
- Credentials logged anywhere
- User enumeration possible
- No rate limiting

---

## 3. Authentication Checklist

### Credential Handling
- [ ] `#[SensitiveParameter]` used on password/secret parameters
- [ ] Credentials not logged in any context
- [ ] Credentials not passed in URLs

### Password Storage
- [ ] Uses `password_hash()` (bcrypt or argon2)
- [ ] Cost factor is adequate (bcrypt ≥ 12, argon2 memory ≥ 64MB)
- [ ] No custom hashing algorithms
- [ ] No MD5, SHA1, or similar weak hashes

### Authentication Flow
- [ ] Generic error messages (no user enumeration)
- [ ] Rate limiting on login attempts
- [ ] Session created only after successful verification
- [ ] Failed attempts don't reveal if email exists

---

## 4. Session Security Checklist

### Cookie Settings
- [ ] HttpOnly = true (prevents XSS theft)
- [ ] Secure = true (HTTPS only in production)
- [ ] SameSite = strict or lax
- [ ] Path set appropriately

### Session Management
- [ ] Session ID is random and secure
- [ ] Session expires after inactivity
- [ ] Logout destroys session completely
- [ ] Session fixation prevention (regenerate on login)

---

## 5. Authorization Checklist

### Role Management
- [ ] Roles are assigned, not granted by user input
- [ ] Role checks are enforced, not bypassed
- [ ] No privilege escalation possible

### Permission System
- [ ] Permissions checked before access
- [ ] Denied access returns 403, not 404
- [ ] Middleware properly intercepts requests

---

## 6. Data Protection Checklist

### Logging
- [ ] No passwords in logs
- [ ] No tokens in logs
- [ ] No credentials in error messages

### Error Handling
- [ ] Stack traces not exposed in production
- [ ] Generic authentication errors
- [ ] No sensitive data in exceptions

### Input Validation
- [ ] Email validation
- [ ] Password minimum requirements
- [ ] No SQL injection (parameterized queries)

---

## 7. Common Vulnerabilities

### OWASP Top 10 (Auth-Relevant)

| Vulnerability | Check |
|---------------|-------|
| A01:2021 Broken Access Control | Authorization enforced? |
| A02:2021 Cryptographic Failures | Passwords hashed? |
| A03:2021 Injection | Parameterized queries? |
| A04:2021 Insecure Design | Generic errors? |
| A05:2021 Security Misconfiguration | Secure defaults? |
| A07:2021 Identification and Auth Failures | Rate limiting? Session secure? |

---

## 8. Security Findings Template

### Finding: <title>
- **Severity:** Critical / High / Medium / Low
- **Location:** file:line
- **Description:** What was found
- **Impact:** Why it matters
- **Recommendation:** How to fix
- **CWE:** Common Weakness Enumeration (if applicable)

---

## 9. Decision

- **Approved**: No critical or high issues
- **Needs Work**: Medium issues that should be fixed
- **Blocked**: Critical/high issues must be resolved

---

## 10. Remediation Priority

| Severity | Must Fix Before Release |
|----------|------------------------|
| Critical | Yes |
| High | Yes |
| Medium | Before production |
| Low | When convenient |
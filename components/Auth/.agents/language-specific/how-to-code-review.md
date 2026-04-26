# Enterprise-Grade Auth Code Review

> **Purpose**
>
> This document defines a result-oriented, enterprise-grade code review process for the **Avax Auth** framework (
> authentication/authorization system).
>
> This is **not** a PR review. The output must enable a clear technical decision about the system's future.

---

## System Identity

- **System Type:** Authentication/Authorization Framework (Pure PHP 8.3+)
- **Primary Consumers:** PHP application developers
- **Runtime Context:** HTTP requests, CLI, worker processes
- **Lifecycle:** Initial development

---

## Review Contract

### What This Review IS

- Auth-specific architecture analysis
- Security design validation
- Identity flow correctness
- Session and credential handling evaluation

### What This Review IS NOT

- Style or formatting review
- Line-by-line refactor suggestions
- General PHP patterns not specific to auth

---

## Expected Final Outcomes

1. **Is the auth system fundamentally sound?**
2. **Is the security design correct?**
3. **What is the correct next action?**

**Decisions:**

- ✅ **Keep and Improve**: system is sound, proceed
- ⚠️ **Redesign**: core security assumptions need rework
- 🚨 **Rewrite Candidate**: foundational security design is flawed

---

## Hard Gates

Stop immediately if:

- Authentication flow cannot be traced end-to-end
- No clear separation between Identity, Session, and Access Control
- Security invariants are not explicit

---

## Auth-Specific Review Checklist

### Authentication Flow

- [ ] Credentials marked with `#[SensitiveParameter]`
- [ ] Passwords hashed (bcrypt/argon2), never plain text
- [ ] Auth failures are generic (no user enumeration)
- [ ] Rate limiting implemented

### Session Management

- [ ] Session tokens are random and secure
- [ ] Cookies are HttpOnly, Secure, SameSite
- [ ] Logout destroys session
- [ ] Session expiry enforced

### Authorization (Access Control)

- [ ] Roles and permissions clearly separated
- [ ] Middleware properly enforces access
- [ ] No privilege escalation possible

### Error Handling

- [ ] No sensitive data in error messages
- [ ] Exceptions don't leak stack traces
- [ ] Auth failures are logged appropriately (without sensitive data)

### Architecture

- [ ] Clear separation: Actions, Adapters, Contracts
- [ ] No tight coupling between adapters
- [ ] Dependency flow is inward (Actions → Contracts → Adapters)

---

## Finding Template

### Finding: <title>

- **Symptom:** …
- **Root Cause:** …
- **Impact:** …
- **Evidence:** file:line
- **Risk Level:** Low / Medium / High / Rewrite Risk

---

## Decision

- **Keep and Improve**
- **Redesign**
- **Rewrite Candidate**

### Justification

[Clear explanation referencing findings]

---

## Next Steps

### If Keep and Improve

1. …
2. …
3. …

### If Redesign

1. …
2. …
3. …

### If Rewrite

1. …
2. …
3. …
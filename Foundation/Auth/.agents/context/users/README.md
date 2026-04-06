# User Personas

## 1. Application Developer (Primary)

**Profile**: PHP developer building a web application

**Goals**:
- Add authentication quickly without reinventing the wheel
- Understand how auth works (no magic)
- Customize the auth flow if needed
- Keep users' data secure

**Pain Points**:
- Framework-specific auth that's hard to move
- Unclear error messages
- Security holes they don't know about

**Needs from Avax Auth**:
- Clear documentation
- Simple API: `$auth->login($credentials)`
- Drop-in solution with good defaults
- Flexibility to swap components

**Quote**: "I just want secure auth that works and doesn't require a PhD to understand."

## 2. Security Engineer

**Profile**: Focused on application security

**Goals**:
- Ensure auth follows OWASP guidelines
- Audit auth implementation easily
- No hidden vulnerabilities

**Pain Points**:
- "Magic" auth they can't audit
- Insecure defaults
- No logging/audit trail

**Needs from Avax Auth**:
- Security-first design
- Clear security documentation
- Audit-friendly code
- Rate limiting, secure sessions

**Quote**: "Make it secure by default so I don't have to check every config."

## 3. End User

**Profile**: Person using an app built with Avax Auth

**Goals**:
- Log in safely
- Manage their session
- Reset password if forgotten

**Pain Points**:
- Confusing error messages
- Doesn't know if login is secure
- Session gets hijacked

**Needs from Avax Auth** (via app developer):
- Generic error messages (don't reveal if email exists)
- Secure session cookies
- Clear feedback on login failure

**Quote**: "Just let me log in safely and don't tell hackers my email exists."

---

## Persona Quotes Summary

| Persona | Key Quote |
|---------|-----------|
| Developer | "I just want secure auth that works." |
| Security | "Make it secure by default." |
| End User | "Don't leak my email to hackers." |
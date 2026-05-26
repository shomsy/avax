# Common Identity Mistakes

## Mistake: Bypassing Identity Component

### What People/AI Do

Other components directly check tokens, sessions, or authentication state instead of going through Identity's public surface.

### Why It Happens

It is faster to access the token store directly than to call through Identity. The developer is in a hurry and thinks "I just need to check if this token is valid."

### What Correct Looks Like

Always delegate to Identity's public surface. Call `$identity->validateToken($token)` not `$tokenStore->find($token)`.

### How to Detect It

- Import of internal Identity classes outside Identity component
- Direct calls to TokenValidator, SessionStore, or Authenticate from other components
- Duplicated token validation logic outside Identity

### How to Fix It

Replace direct access with Identity public surface calls. If the public surface does not expose what you need, extend it through the correct governance process.

### Related

- ADR 0001: Token Lifecycle Separation
- Architecture Rule: "All authentication and authorization must flow through Identity component"

---

## Mistake: Authorization Without Authentication

### What People/AI Do

Checking authorization (can the user do X?) without verifying authentication (is the user who they say they are?).

### Why It Happens

The developer assumes that if a request reaches the authorization check, authentication already happened. This is not always true — middleware ordering can change.

### What Correct Looks Like

Always authenticate first, then authorize. The AuthMiddleware must run before any authorization middleware.

### How to Detect It

- Authorization capability called without prior authentication context
- Authorization checks that assume identity is already verified

### How to Fix It

Ensure authentication middleware is registered before authorization middleware in the pipeline. Authorization should refuse to operate without authenticated context.

### Related

- Dictionary: Authentication, Authorization
- Architecture Rule: "Authentication and authorization are separate concerns"

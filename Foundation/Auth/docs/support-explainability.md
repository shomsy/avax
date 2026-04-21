# Support Explainability

Version: 1.0.0
Status: Normative / Local
Scope: `Avax\Auth\` error and diagnostic messages

This document provides human-readable explanations for common access/authorization failures.

---

## Runtime Surface

These explanations are not docs-only. The package publishes a runtime-safe
operator surface through:

- `Avax\Auth\System\Capabilities\Diagnostics\Explainability\AuthIssueExplainer`
- `Avax\Auth\System\Auth::explainAccessDenied()`
- `Avax\Auth\System\Auth::explainStepUpRequired()`
- `Avax\Auth\System\Auth::explainSenderConstraintFailure()`
- `Avax\Auth\System\Auth::explainSessionRevocation()`
- `Avax\Auth\System\Auth::explainTrustedDeviceDecision()`

Executable evidence:

- `tests/Capabilities/Explainability/AuthIssueExplainerTest.php`
- `tests/System/AuthTest.php`

---

## Access Denied

### Message

```
Access denied to resource {resource} for identity {identity}
```

### Meaning

The identity exists but lacks required permission for the requested resource.

### Resolution

1. Verify identity has required role/permission
2. Check tenant boundaries (cross-tenant access denied)
3. Review authorization policy for resource

---

## Step-Up Required

### Message

```
Step-up authentication required for {action}
```

### Meaning

The action requires stronger authentication (MFA) than currently provided.

### Resolution

1. Complete MFA challenge (TOTP, backup code)
2. If MFA not enrolled, contact admin
3. Policy may require MFA for this action level

---

## Sender-Constraint Failed

### Message

```
Sender constraint validation failed: {reason}
```

### Reasons:

| Reason                   | Meaning                             |
|--------------------------|-------------------------------------|
| `DPOP_INVALID_TOKEN`     | DPoP JWT is malformed or expired    |
| `DPOP_SIGNATURE_INVALID` | Proof doesn't match binding key     |
| `MTLS_MISSING_CERT`      | mTLS required but no client cert    |
| `MTLS_CHAIN_INVALID`     | Certificate chain not trusted       |
| `BINDING_MISMATCH`       | Token key doesn't match client cert |

### Resolution

1. **DPOP**: Regenerate DPoP proof with valid access token
2. **mTLS**: Configure client certificate for request
3. **Chain**: Verify CA is in trusted store
4. **Binding**: Ensure DPoP key matches certificate public key

---

## Session Revoke Did Not Propagate

### Message

```
Session {sessionId} revoked locally; propagation status: {status}
```

### Statuses:

| Status       | Meaning                                 |
|--------------|-----------------------------------------|
| `LOCAL_ONLY` | Revoked locally, RP propagation pending |
| `PARTIAL`    | Some RPs notified, others failed        |
| `PROPAGATED` | All RPs notified                        |

### Resolution

1. **LOCAL_ONLY**: Normal; logout propagates async
2. **PARTIAL**: Check RP endpoint health
3. **PROPAGATED**: All sessions terminated

---

## Tenant Boundary Violation

### Message

```
Access denied: identity {identity} in tenant {tenantA} cannot access resource in tenant {tenantB}
```

### Meaning

Cross-tenant access attempted and denied.

### Resolution

1. Verify correct tenant context
2. Request cross-tenant access if business justified
3. Federation may enable cross-org access

---

## Rate Limited

### Message

```
Rate limit exceeded: {limit} requests per {window}. Retry after {retryAfter} seconds.
```

### Meaning

Too many requests in time window.

### Resolution

1. Wait specified time before retry
2. Implement exponential backoff
3. Contact admin for rate limit increase

---

## Incident Playbooks

### 1. Account Lockout

1. Verify identity not locked due to failed attempts
2. Check if MFA failure lockout
3. Initiate account recovery if needed

### 2. Token Leak Suspected

1. Revoke all sessions immediately: `RevokeSession::revokeForUser($userId, '*')`
2. Force password reset
3. Rotate any API keys
4. Audit logs for unauthorized access

### 3. MFA Bypass Request

1. Verify requester identity (call, video)
2. Document approval in audit
3. Temporarily disable MFA requirement
4. Re-enable after action completion

---

*Part of Auth support documentation.*

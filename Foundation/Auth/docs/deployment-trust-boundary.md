# Deployment Trust Boundary

Version: 1.0.0
Status: Normative / Local
Scope: `Avax\Auth\` deployment configuration

This document defines the trust boundaries for deploying Auth with sender-constrained tokens (DPoP, mTLS).

---

## Deployment Profiles

### 1. Direct TLS Termination

```
[Client] ---TLS---> [Application Server] ---trust---
```

**Configuration:**
- TLS terminates at the application server
- `X-Forwarded-For` used for client IP
- No client certificate required

**Headers trusted:**
- `X-Forwarded-For`
- `X-Forwarded-Proto`
- `X-Forwarded-Host`

---

### 2. Trusted Reverse Proxy

```
[Client] ---TLS---> [Reverse Proxy] ---PLAINTLS---> [App]
```

**Configuration:**
- Proxy validates client (optional mTLS)
- Proxy adds forwarding headers
- Application trusts proxy via `TrustedProxies` configuration

**Headers trusted:**
- `X-Forwarded-For` (validated by proxy)
- `X-Client-Cert` (if proxy forwards)
- `X-TLS-Client-Cert-Verify` (proxy validation result)

**Headers FORBIDDEN from untrusted:**
- `X-Client-Cert` from direct client connections
- Any header starting with `X-Client-` from external sources

---

### 3. mTLS at Edge

```
[Client] ---mTLS---> [Edge] ---mTLS---> [App]
```

**Configuration:**
- Client presents certificate at edge
- Edge forwards certificate metadata
- Application validates certificate chain

**Certificate Propagation:**
- `X-Client-Cert` contains PEM-encoded client cert
- `X-Client-Cert-Fingerprint` contains SHA256 hash
- `X-TLS-Client-Cert-Verify` contains verification result (SUCCESS/FAILED)

---

## Proxy Contract

### Required Headers

| Header | Source | Trust Level |
|--------|--------|-------------|
| `X-Forwarded-For` | Proxy | Trusted if proxy in `TrustedProxies` |
| `X-Forwarded-Proto` | Proxy | Trusted |
| `X-Client-Cert` | Proxy only | Trusted from proxy only |

### Forbidden Headers

| Header | Source | Reason |
|--------|--------|--------|
| `X-Client-Cert` | Direct client | Must come from proxy |
| `X-TLS-Client-*` | Direct client | Proxy-only metadata |

---

## Smoke Tests

### Test 1: Wrong Forwarded Headers

```php
// Send request with forged X-Forwarded-For
$_SERVER['HTTP_X_FORWARDED_FOR'] = '1.2.3.4';
// Expected: Trust proxy configuration validates source
```

### Test 2: Missing Client Cert Metadata

```php
// mTLS request without client cert
$_SERVER['HTTP_X_CLIENT_CERT'] = null;
// Expected: Graceful degradation, no DPoP/mTLS binding
```

### Test 3: Broken DPoP Proof

```php
// DPoP header with invalid JWT
$request->headers->set('DPoP', 'invalid.jwt.token');
// Expected: 401 with DPoP error
```

### Test 4: Cert/Key Mismatch

```php
// Client cert doesn't match DPoP key
$clientCert = $request->getClientCert();
$dpopKey = extractKeyFromDPoP($request->headers->get('DPoP'));
// Expected: Binding validation failure
```

---

## Operator Checklist

### mTLS Chain Validation

1. Verify CA certificate is not expired
2. Verify client certificate is not expired  
3. Verify certificate chain integrity
4. Check revocation lists (CRL/OCSP)
5. Verify certificate matches client identity

### Unsafe Deployment Mode Detection

**Warning if:**
- Running without TLS in production
- Client cert validation disabled
- Proxy not in trusted list
- DPoP/mTLS disabled in production mode

---

## Support Diagnostics

### Sender-Constraint Failure Messages

| Error | Meaning | Resolution |
|-------|---------|-------------|
| `DPOP_INVALID_TOKEN` | DPoP JWT malformed | Check token format |
| `DPOP Proof Invalid` | Signature verification failed | Check key binding |
| `MTLS_MISSING_CERT` | No client certificate | Configure mTLS |
| `MTLS_CHAIN_INVALID` | Certificate chain invalid | Check CA certificates |
| `BINDING_MISMATCH` | Cert/key doesn't match proof | Check client configuration |

---

*Part of Auth deployment documentation.*
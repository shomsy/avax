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

| Header              | Source     | Trust Level                          |
|---------------------|------------|--------------------------------------|
| `X-Forwarded-For`   | Proxy      | Trusted if proxy in `TrustedProxies` |
| `X-Forwarded-Proto` | Proxy      | Trusted                              |
| `X-Client-Cert`     | Proxy only | Trusted from proxy only              |

### Forbidden Headers

| Header           | Source        | Reason               |
|------------------|---------------|----------------------|
| `X-Client-Cert`  | Direct client | Must come from proxy |
| `X-TLS-Client-*` | Direct client | Proxy-only metadata  |

---

## Executable Smoke Tests

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

## Deployment Trust Smoke Tests

These tests verify the deployment trust boundary in executable package-owned
checks.

### Test Scenarios

#### 1. Reverse Proxy Header Trust

```php
// Test: Wrong forwarded headers are rejected
$headers = [
    'X-Forwarded-For' => '10.0.0.1',  // Should be rejected from untrusted proxy
];
$verifier = new VerifyTrustedProxyHeaders(trustedProxies: ['proxy.trusted.com']);
$result = $verifier->verify($headers);
// Expected: Reject with TRUSTED_PROXY_INVALID
```

#### 2. mTLS Chain Propagation

```php
// Test: Client certificate propagation through proxy
$headers = [
    'X-Client-Cert' => $certFromUntrustedSource,
];
$verifier = new VerifyMtlsChain(trustedCaCertificates: $trustedCa);
$result = $verifier->verify($headers);
// Expected: Reject with MTLS_CHAIN_INVALID
```

#### 3. DPoP Proof Mismatch

```php
// Test: DPoP proof with wrong method/URI
$proof = CreateDpoProof(
    privateKey: $clientKey,
    method: 'GET',           // Actual method is POST
    uri: '/api/token',       // Actual URI differs
);
$verifier = new VerifyDpopProof();
$result = $verifier->verify($proof, expectedMethod: 'POST', expectedUri: '/api/token');
// Expected: Reject with DPOP_PROOF_INVALID
```

#### 4. Mixed Proxy + Sender-Constraint

```php
// Test: Proxy forwards headers AND client uses DPoP
$headers = [
    'X-Forwarded-For' => '10.0.0.1',
    'X-Client-Cert' => $cert,
    'DPoP' => $proof,
];
$verifier = new VerifySenderConstraint(
    trustedProxies: ['proxy.trusted.com'],
    requiredConstraint: OAuthSenderConstraintType::DPOP
);
$result = $verifier->verify($headers, envelope: $tokenEnvelope);
// Expected: Accept - both proxy headers and DPoP are valid
```

### Executable Evidence

- `integrations/http/VerifyTrustedProxyHeaders.php`
- `integrations/http/DetectUnsafeDeploymentMode.php`
- `integrations/http/VerifyOAuthSenderConstraint.php`
- `tests/Integrations/Http/DeploymentTrustBoundaryTest.php`
- `tests/Integrations/Http/VerifyOAuthSenderConstraintTest.php`

Run:

```bash
php composer.phar test -- \
  tests/Integrations/Http/DeploymentTrustBoundaryTest.php \
  tests/Integrations/Http/VerifyOAuthSenderConstraintTest.php
```

### Manual Operator Checklist

Before production deployment, verify:

- [ ] TLS certificate is valid and not expired
- [ ] Client certificates are valid and not expired
- [ ] Certificate chain integrity verified
- [ ] Trusted proxy list is configured
- [ ] DPoP/mTLS policy matches deployment profile
- [ ] Sender-constraint failures generate audit events

---

## Support Diagnostics

### Sender-Constraint Failure Messages

| Error                | Meaning                       | Resolution                 |
|----------------------|-------------------------------|----------------------------|
| `DPOP_INVALID_TOKEN` | DPoP JWT malformed            | Check token format         |
| `DPOP Proof Invalid` | Signature verification failed | Check key binding          |
| `MTLS_MISSING_CERT`  | No client certificate         | Configure mTLS             |
| `MTLS_CHAIN_INVALID` | Certificate chain invalid     | Check CA certificates      |
| `BINDING_MISMATCH`   | Cert/key doesn't match proof  | Check client configuration |

---

*Part of Auth deployment documentation.*

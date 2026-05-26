# Common Authentication Mistakes

## 1. Treating Token Presence as Authentication

**Mistake**: Assuming that any request with a token is authenticated.

**Problem**: Any client can send any token string. The token must be validated (signature, expiry, issuer, audience, revocation) before trust is granted.

**Correct Approach**: Every token goes through the token validation capability before any trust is granted. Validation produces either a verified identity or a denial.

## 2. Information Leakage in Error Messages

**Mistake**: Returning specific error messages like "user not found" or "wrong password."

**Problem**: Attackers use these messages to enumerate valid users and test credentials.

**Correct Approach**: Return generic error messages ("invalid credentials") for all authentication failures. Log specific details server-side for debugging.

## 3. Credential Logging

**Mistake**: Logging passwords, tokens, or other credentials in error messages, debug output, or observability events.

**Problem**: Credential exposure in logs creates a secondary attack vector.

**Correct Approach**: Never log credential values. Log authentication events (attempt started, succeeded, failed) without credential data.

## 4. Failing Open on Uncertainty

**Mistake**: Allowing access when authentication state is unclear or validation fails unexpectedly.

**Problem**: Any uncertainty in authentication is a potential security gap.

**Correct Approach**: Fail closed. If authentication state cannot be definitively verified, deny access and log the event.

## 5. Mixing Authentication and Authorization

**Mistake**: Using the presence of authenticated identity as proof of permission.

**Problem**: Authentication proves who you are. Authorization determines what you can do. They are separate concerns.

**Correct Approach**: Authentication produces identity. Authorization consumes identity and decides permissions. Never skip authorization because authentication succeeded.

## 6. Not Validating External Identity Providers

**Mistake**: Trusting OAuth/OIDC responses without full validation.

**Problem**: Unvalidated external identity can be forged or manipulated.

**Correct Approach**: Validate ID token signature, issuer, audience, expiry, and nonce before trusting any claims from external providers.

## 7. Static Authentication State in Long-Lived Workers

**Mistake**: Caching authentication state in static or mutable global state in long-lived workers.

**Problem**: State leaks between requests, causing cross-request identity contamination.

**Correct Approach**: Load and validate authentication state per-request. Never persist identity state between requests in long-lived workers.

## 8. Not Observing Authentication Events

**Mistake**: Running authentication without observable events.

**Problem**: Without observability, attacks, brute force attempts, and anomalous patterns go undetected.

**Correct Approach**: Every authentication event (attempt, success, failure, MFA challenge) produces an observable event for security monitoring.

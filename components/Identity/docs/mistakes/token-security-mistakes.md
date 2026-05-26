# Token Security Mistakes

## 1. Assuming JWT Tokens Are Revocable

**Mistake**: Treating JWT access tokens as instantly revocable.

**Problem**: JWT tokens are self-contained. Once issued, they are valid until expiry. There is no server-side state to delete.

**Correct Approach**: Use short access token expiry (minutes, not hours) with refresh tokens that ARE revocable. For immediate revocation, maintain a deny list or bind tokens to a server-side session.

## 2. Not Validating Token Signature

**Mistake**: Accepting tokens without verifying their cryptographic signature.

**Problem**: Any client can forge a token with arbitrary claims if signature validation is skipped.

**Correct Approach**: Always verify the token signature using the correct key before trusting any claims. Validate the signing algorithm to prevent algorithm confusion attacks.

## 3. Ignoring Token Audience and Issuer

**Mistake**: Validating only signature and expiry, ignoring audience and issuer.

**Problem**: A valid token from a different service or intended for a different audience is not proof of identity for this service.

**Correct Approach**: Validate signature, expiry, issuer, and audience. All must match expected values before trusting the token.

## 4. Storing Tokens Insecurely on Client

**Mistake**: Storing access tokens in localStorage or other accessible client storage.

**Problem**: XSS attacks can steal tokens from localStorage, enabling token replay.

**Correct Approach**: Store refresh tokens in httpOnly, secure, sameSite cookies. Keep access tokens in memory only. Short access token expiry limits the window of exposure.

## 5. Using Tokens Without Expiry

**Mistake**: Issuing tokens with no expiration or extremely long expiration.

**Problem**: Compromised tokens remain valid indefinitely, creating a persistent security gap.

**Correct Approach**: Access tokens should expire in minutes. Refresh tokens expire in hours or days but are revocable. Implement token rotation for refresh tokens.

## 6. Not Rotating Refresh Tokens

**Mistake**: Reusing the same refresh token indefinitely.

**Problem**: A stolen refresh token can be used alongside the legitimate user's refresh token without detection.

**Correct Approach**: Rotate refresh tokens on each use. If a rotated refresh token is used, detect the concurrent use and revoke the session.

## 7. Logging Token Values

**Mistake**: Including full token values in logs, error messages, or observability events.

**Problem**: Token exposure in logs enables token theft from log storage.

**Correct Approach**: Never log full token values. Log token identifiers or truncated references (first/last few characters) for debugging. Never log the full token.

## 8. Not Binding Tokens to Identity

**Mistake**: Issuing tokens that are not bound to the authenticated identity and session.

**Problem**: Tokens become transferable credentials that can be used by any attacker who obtains them.

**Correct Approach**: Bind tokens to the authenticated identity and optionally the session. Validate the binding during token validation. Consider additional binding (IP, device fingerprint) for higher assurance.

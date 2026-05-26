# Token Validation Flow

## Overview

The token validation flow verifies that a presented token is authentic, unexpired, and authorized for the requested operation. This flow runs on every authenticated request.

## Sequence Diagram

```mermaid
sequenceDiagram
    participant Client
    participant PublicSurface
    participant AuthMiddleware
    participant TokenValidationCapability
    participant TokenParser
    participant SignatureVerifier
    participant RevocationChecker
    participant IdentityReconstructor
    participant Observability

    Client->>PublicSurface: GET /protected (Authorization: Bearer <token>)
    PublicSurface->>AuthMiddleware: authenticateRequest(request)

    AuthMiddleware->>TokenValidationCapability: validateToken(token)
    TokenValidationCapability->>Observability: event: token_validation_started

    TokenValidationCapability->>TokenParser: parse(token)

    alt Parse Succeeds
        TokenParser-->>TokenValidationCapability: tokenClaims

        TokenValidationCapability->>SignatureVerifier: verifySignature(tokenClaims)

        alt Signature Valid
            SignatureVerifier-->>TokenValidationCapability: signatureValid = true

            TokenValidationCapability->>TokenValidationCapability: checkExpiry(tokenClaims)

            alt Not Expired
                TokenValidationCapability->>TokenValidationCapability: checkIssuer(tokenClaims)

                alt Issuer Valid
                    TokenValidationCapability->>TokenValidationCapability: checkAudience(tokenClaims)

                    alt Audience Valid
                        TokenValidationCapability->>RevocationChecker: isRevoked(tokenClaims)

                        alt Not Revoked
                            RevocationChecker-->>TokenValidationCapability: revoked = false

                            TokenValidationCapability->>IdentityReconstructor: reconstructIdentity(tokenClaims)
                            IdentityReconstructor-->>TokenValidationCapability: identity

                            TokenValidationCapability->>Observability: event: token_validation_succeeded
                            TokenValidationCapability-->>AuthMiddleware: validIdentity(identity)
                            AuthMiddleware-->>PublicSurface: authenticatedRequest(identity)
                            PublicSurface-->>Client: 200 OK (response)

                        else Revoked
                            RevocationChecker-->>TokenValidationCapability: revoked = true
                            TokenValidationCapability->>Observability: event: token_validation_failed(revoked)
                            TokenValidationCapability-->>AuthMiddleware: validationFailed
                            AuthMiddleware-->>PublicSurface: unauthenticated
                            PublicSurface-->>Client: 401 Unauthorized
                        end
                    else Audience Invalid
                        TokenValidationCapability->>Observability: event: token_validation_failed(audience)
                        TokenValidationCapability-->>AuthMiddleware: validationFailed
                        AuthMiddleware-->>PublicSurface: unauthenticated
                        PublicSurface-->>Client: 401 Unauthorized
                    end
                else Issuer Invalid
                    TokenValidationCapability->>Observability: event: token_validation_failed(issuer)
                    TokenValidationCapability-->>AuthMiddleware: validationFailed
                    AuthMiddleware-->>PublicSurface: unauthenticated
                    PublicSurface-->>Client: 401 Unauthorized
                end
            else Expired
                TokenValidationCapability->>Observability: event: token_validation_failed(expired)
                TokenValidationCapability-->>AuthMiddleware: validationFailed
                AuthMiddleware-->>PublicSurface: unauthenticated
                PublicSurface-->>Client: 401 Unauthorized
            end
        else Signature Invalid
            SignatureVerifier-->>TokenValidationCapability: signatureValid = false
            TokenValidationCapability->>Observability: event: token_validation_failed(signature)
            TokenValidationCapability-->>AuthMiddleware: validationFailed
            AuthMiddleware-->>PublicSurface: unauthenticated
            PublicSurface-->>Client: 401 Unauthorized
        end
    else Parse Failed
        TokenParser-->>TokenValidationCapability: parseFailed
        TokenValidationCapability->>Observability: event: token_validation_failed(malformed)
        TokenValidationCapability-->>AuthMiddleware: validationFailed
        AuthMiddleware-->>PublicSurface: unauthenticated
        PublicSurface-->>Client: 401 Unauthorized
    end
```

## Validation Steps

1. **Parse**: Extract and decode the token structure
2. **Signature**: Verify the cryptographic signature
3. **Expiry**: Check the token has not expired
4. **Issuer**: Verify the token was issued by a trusted issuer
5. **Audience**: Verify the token is intended for this audience
6. **Revocation**: Check if the token has been revoked
7. **Identity Reconstruction**: Rebuild the identity claim set from valid token claims

## Failure Modes

- Malformed token: 401, event logged
- Invalid signature: 401, event logged
- Expired token: 401, event logged (client should refresh)
- Invalid issuer: 401, event logged (possible token injection)
- Invalid audience: 401, event logged (possible token misuse)
- Revoked token: 401, event logged (possible compromise)

## Security Considerations

- Every validation step must pass; any failure denies access
- Validation produces observable events for security monitoring
- Generic error messages prevent information leakage
- Validation is stateless for access tokens (except revocation check)
- No caching of validation results between requests in long-lived workers

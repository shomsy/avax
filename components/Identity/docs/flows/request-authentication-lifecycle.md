# Request Authentication Lifecycle

## Overview

The request authentication lifecycle describes how every incoming request is authenticated, from initial receipt through identity attachment and downstream authorization readiness.

## Sequence Diagram

```mermaid
sequenceDiagram
    participant Client
    participant HttpServer
    participant PublicSurface
    participant AuthMiddleware
    participant TenantResolver
    participant TokenValidationCapability
    participant SessionCapability
    participant IdentityContext
    participant AuthorizationBoundary
    participant BusinessFlow
    participant Observability

    Client->>HttpServer: HTTP Request
    HttpServer->>PublicSurface: route(request)

    PublicSurface->>AuthMiddleware: before(auth)

    AuthMiddleware->>Observability: event: request_auth_started

    AuthMiddleware->>AuthMiddleware: extractToken(request)

    alt Token Present
        AuthMiddleware->>TokenValidationCapability: validateToken(token)

        alt Token Valid
            TokenValidationCapability-->>AuthMiddleware: identityClaims

            AuthMiddleware->>TenantResolver: resolveTenant(request, identityClaims)
            TenantResolver-->>AuthMiddleware: tenantContext

            alt Tenant Valid
                AuthMiddleware->>IdentityContext: attach(identityClaims, tenantContext)
                IdentityContext-->>AuthMiddleware: attached

                AuthMiddleware->>Observability: event: request_auth_succeeded
                AuthMiddleware-->>PublicSurface: authenticated(request)

                PublicSurface->>AuthorizationBoundary: checkPermission(request, identity)

                alt Authorized
                    AuthorizationBoundary-->>PublicSurface: allowed
                    PublicSurface->>BusinessFlow: execute(request, identity)
                    BusinessFlow-->>PublicSurface: response
                    PublicSurface-->>HttpServer: response
                    HttpServer-->>Client: HTTP Response
                else Not Authorized
                    AuthorizationBoundary-->>PublicSurface: denied
                    PublicSurface->>Observability: event: authorization_denied
                    PublicSurface-->>HttpServer: 403 Forbidden
                    HttpServer-->>Client: 403 Forbidden
                end
            else Tenant Invalid
                AuthMiddleware->>Observability: event: tenant_resolution_failed
                AuthMiddleware-->>PublicSurface: unauthorized
                PublicSurface-->>HttpServer: 401 Unauthorized
                HttpServer-->>Client: 401 Unauthorized
            end
        else Token Invalid
            TokenValidationCapability-->>AuthMiddleware: validationFailed
            AuthMiddleware->>Observability: event: request_auth_failed(invalid_token)
            AuthMiddleware-->>PublicSurface: unauthorized
            PublicSurface-->>HttpServer: 401 Unauthorized
            HttpServer-->>Client: 401 Unauthorized
        end

    else No Token, Session Present
        AuthMiddleware->>SessionCapability: loadSession(request)

        alt Session Valid
            SessionCapability-->>AuthMiddleware: identityClaims

            AuthMiddleware->>TenantResolver: resolveTenant(request, identityClaims)
            TenantResolver-->>AuthMiddleware: tenantContext

            alt Tenant Valid
                AuthMiddleware->>IdentityContext: attach(identityClaims, tenantContext)
                IdentityContext-->>AuthMiddleware: attached

                AuthMiddleware->>Observability: event: request_auth_succeeded(session)
                AuthMiddleware-->>PublicSurface: authenticated(request)
                PublicSurface->>BusinessFlow: execute(request, identity)
                BusinessFlow-->>PublicSurface: response
                PublicSurface-->>HttpServer: response
                HttpServer-->>Client: HTTP Response
            else Tenant Invalid
                AuthMiddleware->>Observability: event: tenant_resolution_failed
                AuthMiddleware-->>PublicSurface: unauthorized
                PublicSurface-->>HttpServer: 401 Unauthorized
                HttpServer-->>Client: 401 Unauthorized
            end
        else Session Invalid/Expired
            SessionCapability-->>AuthMiddleware: sessionInvalid
            AuthMiddleware->>Observability: event: request_auth_failed(invalid_session)
            AuthMiddleware-->>PublicSurface: unauthorized
            PublicSurface-->>HttpServer: 401 Unauthorized
            HttpServer-->>Client: 401 Unauthorized
        end

    else No Token, No Session
        AuthMiddleware->>Observability: event: request_auth_failed(no_credentials)
        AuthMiddleware-->>PublicSurface: unauthorized
        PublicSurface-->>HttpServer: 401 Unauthorized
        HttpServer-->>Client: 401 Unauthorized
    end
```

## Lifecycle Stages

1. **Request Receipt**: HTTP server receives the request
2. **Credential Extraction**: Auth middleware extracts token or session identifier
3. **Token/Session Validation**: The appropriate validation capability verifies the credential
4. **Tenant Resolution**: Tenant context is resolved and validated against identity
5. **Identity Attachment**: Verified identity and tenant context are attached to the request
6. **Authorization Check**: Authorization boundary checks permissions for the requested operation
7. **Business Execution**: If authorized, the business flow executes with the attached identity

## Failure Modes

- No credentials: 401, request denied
- Invalid token: 401, request denied
- Invalid session: 401, request denied
- Invalid tenant: 401, request denied
- Insufficient permissions: 403, request denied
- Any validation error: fail closed, deny access

## Security Considerations

- Authentication runs before any business logic
- Tenant resolution is validated before data access
- Authorization protects the resource, not only the route
- All stages produce observable events
- No identity state persists between requests in long-lived workers

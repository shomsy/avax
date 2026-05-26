# API Component

> This component owns HTTP API routing, request parsing, response formatting, and API versioning for external-facing endpoints.

## What Lives Here

- **PublicSurface/** — Route registration, API entry points, version negotiation
- **Flows/** — Request-to-response lifecycle for API calls
- **Capabilities/** — Request parser, response builder, content negotiation, error formatter
- **Configuration/** — Route registration, middleware attachment, API version configuration
- **Foundation/** — ApiRequest, ApiResponse, ApiVersion, ErrorPayload value objects

## What Does NOT Live Here

- Business logic or domain behavior (belongs in domain components)
- Authentication and authorization (belongs in Identity component)
- Internal-only routes (belongs in the consuming component or app layer)
- HTML rendering (belongs in web/UI-specific components)

## Ownership Model

Owned by the Platform API team. Public API changes require API compatibility review. Breaking changes must follow the deprecation policy.

## Architectural Intent

API provides a consistent HTTP interface for external consumers. All API requests go through content negotiation, authentication, validation, and formatted responses. API versioning is explicit at the routing level. Internal components must not expose raw HTTP endpoints — they must go through the API component.

## Common Mistakes

1. **Skipping content negotiation** — API must respect Accept headers and return appropriate Content-Type.
2. **Hard-coding status codes in capabilities** — Status codes belong in the API response layer, not in domain logic.
3. **Exposing internal errors to clients** — Error responses must be sanitized and user-safe.
4. **Versioning by copy-paste** — New API versions should not duplicate entire controllers.

## Entry Points

- Start with `PublicSurface/ApiRouter.php` for route registration
- Start with `Capabilities/RequestParser.php` for request handling
- Start with `Capabilities/ResponseBuilder.php` for response formatting

# ADR-0001: HTTP Routing Strategy

## Status

**ACCEPTED**

## Context

AvaX needs a routing strategy that maps HTTP URLs to component Flows. The decision affects:
- Developer experience (how easy is it to add a new route?)
- Performance (how fast can we match a URL to a Flow?)
- Maintainability (how easy is it to understand which Flow handles which URL?)
- Security (can we enforce authorization at the routing level?)

Options considered:
1. **Attribute-based routing**: PHP attributes on Flow classes (`#[Route('/users/{id}', method: 'GET')]`)
2. **Configuration-based routing**: YAML/PHP config files that map URLs to Flows
3. **Convention-based routing**: URL patterns automatically map to Flow names

## Decision

We use **attribute-based routing** with compiled route tables.

Routes are defined as PHP attributes on Flow classes. At boot time, all routes are scanned, compiled, and cached into a fast lookup table. At request time, the router matches the URL against the compiled table.

```php
#[Route('/users/{id}', method: 'GET')]
class GetUserProfile extends Flow
{
    public function execute(Request $request, Response $response): void
    {
        // ...
    }
}
```

### Why This Decision

- Attributes keep the route NEXT TO the Flow that handles it (self-documenting)
- Compiled route tables are fast at request time (no reflection during hot path)
- PHP attributes are type-safe and IDE-friendly
- The route table can be validated at boot time (fail fast on duplicate routes)

### Trade-offs

- Route scanning happens at boot time (acceptable: compile-time cost, not request-time)
- Developers must understand PHP attributes (acceptable: PHP 8.x standard)
- Route conflicts must be detected at boot time (handled by route validation)

## Consequences

- Boot-time route compilation is mandatory
- Route conflicts are BLOCKER errors at boot
- Route tables must be cacheable for production
- Route attributes must be documented in component READMEs

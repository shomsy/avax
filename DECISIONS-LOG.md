# DECISIONS-LOG.md

## Architectural Decisions for HTTP Router

### Decision: Keep and Improve
- **Date:** 2026-01-12
- **Context:** Code review of Router component
- **Decision:** Keep existing architecture, apply incremental improvements
- **Rationale:** Core axis (routes) is stable, no need for rewrite
- **Consequences:** Faster delivery, lower risk

### Decision: Extract RouteMatcher
- **Date:** 2026-01-12
- **Context:** Monolithic Router::resolve
- **Decision:** Separate matching logic into RouteMatcher class
- **Rationale:** SRP, testability
- **Consequences:** Cleaner code, easier testing

### Decision: Centralize Error Handling
- **Date:** 2026-01-12
- **Context:** Inconsistent 404/405 responses
- **Decision:** Introduce ErrorResponseFactory
- **Rationale:** DRY, consistent user experience
- **Consequences:** Better error handling, easier extension

### Decision: Unify Route Registration
- **Date:** 2026-01-12
- **Context:** Dual RouteCollector/RouteRegistry
- **Decision:** Use RouteCollector for all DSL registration
- **Rationale:** Single source of truth
- **Consequences:** Eliminated duplicates, simpler bootstrap
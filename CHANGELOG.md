# CHANGELOG

## [Unreleased]

### Added
- Enterprise-grade Router with clean architecture
- RouteMatcher for separated matching logic
- ErrorResponseFactory for centralized error handling
- Security enhancements: input sanitization, method validation
- Test matrix for optional/wildcard routes
- API stabilization with @internal markers

### Changed
- Router::resolve now uses RouteMatcher
- Route registration unified with RouteCollector
- Fallback handling centralized in ErrorResponseFactory

### Fixed
- Duplicate route registration for closures
- Route path compilation for optional and wildcard parameters
- Cache toolchain with unified RouteCollector

### Performance
- Reduced allocations with immutable RouteDefinitions
- Cached route patterns (planned)

### Documentation
- Added CODE-REVIEW.md with architecture decisions
- Added DECISIONS-LOG.md for future changes

## [v1.2]

- DependencyInjector traits finalized with telemetry, strict autowire controls, and policy extensions.
- Added Container health reporting and validation reports.
- Updated resolver/injection orchestration and provider split for sessions.

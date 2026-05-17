# Term
Diagnostic

# Classification
Observability Component

# Purpose
Health checks, status reporting, and system introspection capabilities. A diagnostic component probes the system to determine whether it is operating correctly, reports the current health state, and provides information useful for debugging and troubleshooting.

# Why Allowed
Diagnostics is a standard term in observability and system health monitoring across the software industry. .NET provides a comprehensive diagnostics framework (`System.Diagnostics`, `Microsoft.Extensions.Diagnostics.HealthChecks`) for health checks, activity tracing, and metrics. Go includes runtime diagnostics through `runtime/pprof`, `net/http/pprof`, and `expvar` for profiling and monitoring. PHP itself includes opcache diagnostics, error reporting, and the `phpinfo()` function for introspection. Framework health check systems in Laravel (`spatie/laravel-health`), Symfony (`symfony/health-check`), and ASP.NET Core (`Microsoft.Extensions.Diagnostics.HealthChecks`) all use diagnostics terminology to describe components that verify system health. A diagnostic component has clear characteristics: it probes a specific subsystem, reports a health status (healthy, degraded, unhealthy), provides details about the failure or success, and is designed to be executed on demand or on a schedule. It is not a generic utility — it is purposeful system introspection.

# Allowed Contexts
- Health checks for subsystems (database, cache, queue, filesystem, external APIs)
- System introspection and status reporting
- Debugging tooling and developer diagnostics
- Observability and monitoring integrations
- Pre-flight checks and readiness probes
- Configuration validation and environment verification

# Forbidden Misuse
- As a generic utility bucket for code that does not fit elsewhere
- As a dumping ground for unrelated debug code, logging helpers, or print statements
- Naming business logic classes as "diagnostics" when they do not perform health checks
- Creating a Diagnostics/ folder that collects every debugging-related snippet
- Using "Diagnostic" to describe error handling or exception management

# Ecosystem References
- https://learn.microsoft.com/en-us/dotnet/core/diagnostics/
- https://pkg.go.dev/net/http/pprof
- https://github.com/spatie/laravel-health
- https://symfony.com/doc/current/components/health_check.html

# Allowed Patterns
- CheckCacheHealth
- DiagnoseCacheConfiguration
- SystemDiagnostics
- DatabaseHealthDiagnostic
- QueueConnectivityDiagnostic
- DiagnoseExternalApiConnectivity

# Forbidden Patterns
- Diagnostics/ (as folder for random utilities or debug helpers)
- DiagnosticManager (managers are forbidden; diagnostics are capabilities, not managers)
- GenericDiagnostic (too vague — what is being diagnosed?)
- DiagnosticHelper (redundant — diagnostic already implies helper behavior)

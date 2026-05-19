# Language Profile: Go

Version: 1.0.0
Status: Normative
Applies when: project `AGENTS.md` declares `go` or inference detects `go.mod`.

## Scope

Go-specific coding, tooling, and quality rules.

## Coding Rules

- follow `gofmt` and `go vet` conventions unconditionally
- prefer short package names that describe capability
- use explicit error handling — do not panic in library code
- keep interfaces small and consumer-defined
- use `context.Context` for cancellation and deadline propagation
- avoid global mutable state
- prefer composition over inheritance (Go has no inheritance)

## Module Rules

- use Go modules (`go.mod`) for dependency management
- keep `go.sum` committed
- pin dependencies to specific versions
- avoid `replace` directives in published modules

## Testing Rules

- use `go test` as the canonical test runner
- table-driven tests preferred for combinatorial cases
- use `t.Helper()` in test utilities
- benchmark performance-critical paths with `testing.B`

## Tooling

- `go fmt` — formatting
- `go vet` — static analysis
- `golangci-lint` — extended linting (when configured)
- `go test -race` — race condition detection
- `go build` — compilation verification

## Architecture Notes

See `.agents/governance/architecture/architecture-standard.md` under the Go
section for Go-specific architecture rules.

Package boundary is more important than deeply descriptive filenames.

## Validation Expectations

- use standard build/compile/lint commands as validation

## Testing Expectations

- 100% pass rate on canonical test suites

## Static Analysis Expectations

- Zero errors at level 5/standard for Go

## Security Expectations

- No high/critical vulnerabilities in dependencies

## Release Expectations

- Artifacts must be versioned and published to private/public registries

## Evidence Expectations

- Validation logs must be attached to release packs

## Common Failure Patterns

- dependency version mismatch, missing lockfiles

## Review Expectations

- strict review for breaking API changes

## Dependency Rules

- pin all dependencies; no wildcards

## Formatting Rules

- follow Go community standard formatting

## Runtime Assumptions

- assumes stable runtime version Go

## Operational Expectations

- process must handle SIGTERM/SIGINT gracefully

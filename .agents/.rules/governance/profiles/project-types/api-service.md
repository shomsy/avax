# Project Type Profile: API Service

Version: 1.0.0
Status: Normative
Applies when: project `AGENTS.md` declares `api-service` or inference detects
an API-first service structure.

## Scope

This profile applies to projects that expose HTTP/gRPC/GraphQL or equivalent
programmatic interfaces as their primary surface.

## Architecture Expectations

- API contract defined before or alongside implementation
- transport layer stays thin — business logic does not live in controllers
- request validation before side effects
- response format is stable and versioned
- error responses are structured and machine-readable

## Security Expectations

- OWASP API baseline applies: `.agents/governance/security/owasp-web-and-api-baseline.md`
- authentication required for non-public endpoints
- rate limiting for externally exposed endpoints
- input sanitization for all external input

## Validation Expectations

- API contract tests or equivalent
- health/readiness endpoints
- error response format tested

## Evidence Expectations

- API contract artifact (OpenAPI, schema, etc.) versioned with code
- deployment evidence includes reachable endpoint verification

## Testing Expectations

- (To be defined: testing expectations for api-service)

## Static Analysis Expectations

- (To be defined: static analysis expectations for api-service)

## Release Expectations

- (To be defined: release expectations for api-service)

## Common Failure Patterns

- (To be defined: common failure patterns for api-service)

## Review Expectations

- (To be defined: review expectations for api-service)

## Dependency Rules

- (To be defined: dependency rules for api-service)

## Formatting Rules

- (To be defined: formatting rules for api-service)

## Runtime Assumptions

- (To be defined: runtime assumptions for api-service)

## Operational Expectations

- (To be defined: operational expectations for api-service)

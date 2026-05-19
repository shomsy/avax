# Project Type Profile: Monorepo

Version: 1.0.0
Status: Normative
Applies when: project `AGENTS.md` declares `monorepo` or inference detects
multiple independent packages in one repository.

## Scope

This profile applies to repositories that contain multiple independently
versioned or deployed packages, services, or applications.

## Architecture Expectations

- each package has clear ownership boundaries
- shared code has explicit dependency rules
- cross-package changes are scoped and documented
- package boundaries are enforceable by tooling when available

## Validation Expectations

- per-package validation possible
- cross-package integration validation defined
- changed-package detection for efficient CI

## Release Expectations

- per-package or coordinated versioning strategy declared
- release scope (which packages) explicit
- rollback strategy per-package when packages deploy independently

## Evidence Expectations

- change scope identifies affected packages
- validation evidence scoped to affected packages

## Testing Expectations

- (To be defined: testing expectations for monorepo)

## Static Analysis Expectations

- (To be defined: static analysis expectations for monorepo)

## Security Expectations

- (To be defined: security expectations for monorepo)

## Common Failure Patterns

- (To be defined: common failure patterns for monorepo)

## Review Expectations

- (To be defined: review expectations for monorepo)

## Dependency Rules

- (To be defined: dependency rules for monorepo)

## Formatting Rules

- (To be defined: formatting rules for monorepo)

## Runtime Assumptions

- (To be defined: runtime assumptions for monorepo)

## Operational Expectations

- (To be defined: operational expectations for monorepo)

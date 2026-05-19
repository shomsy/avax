# Project Type Profile: CLI

Version: 1.0.0
Status: Normative
Applies when: project `AGENTS.md` declares `cli` or inference detects a
command-line tool structure.

## Scope

This profile applies to projects that provide command-line interfaces as their
primary user surface.

## Architecture Expectations

- clear separation between CLI parsing and core logic
- exit codes are meaningful and documented
- help text is accurate and generated from the same source as behavior
- stdin/stdout/stderr usage is deliberate

## Validation Expectations

- automated tests for argument parsing and core commands
- error output tested for user clarity
- cross-platform behavior documented when relevant

## Evidence Expectations

- help text output captured as evidence
- exit code behavior documented

## Testing Expectations

- (To be defined: testing expectations for cli)

## Static Analysis Expectations

- (To be defined: static analysis expectations for cli)

## Security Expectations

- (To be defined: security expectations for cli)

## Release Expectations

- (To be defined: release expectations for cli)

## Common Failure Patterns

- (To be defined: common failure patterns for cli)

## Review Expectations

- (To be defined: review expectations for cli)

## Dependency Rules

- (To be defined: dependency rules for cli)

## Formatting Rules

- (To be defined: formatting rules for cli)

## Runtime Assumptions

- (To be defined: runtime assumptions for cli)

## Operational Expectations

- (To be defined: operational expectations for cli)

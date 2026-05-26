# Remediation Backlog

Generated: 2026-05-26

## PHPStan Type Issues

- Severity: HIGH/MEDIUM.
- Baseline: `.agents/management/baselines/phpstan-baseline.json`
- Estimated slices:
  1. Identity PHPStan safety: fix Identity access/auth/tokens type issues.
  2. DataStack persistence SQL-injection test typing.
  3. Test assertion cleanup where PHPStan reports always-true/always-false assertions.
  4. Queue worker iterable types.

## Missing Self-Explaining Docs

- Severity: HIGH/MEDIUM/LOW.
- Baseline: `.agents/management/baselines/self-explaining-architecture-baseline.json`
- Estimated slices:
  1. Component roots with missing README.
  2. Important boundaries missing dictionaries.
  3. ADR folders for high-change/high-risk areas.
  4. Complex flow diagrams, starting with Identity and ApplicationWorkflow.

## Shallow Tests

- Severity: HIGH/MEDIUM.
- Baseline: `.agents/management/baselines/shallow-tests-baseline.json`
- Estimated slices:
  1. Security-sensitive Identity/Auth/Token/Access/Tenant tests.
  2. Security and HTTP security tests with missing negative/failure assertions.
  3. Reflection-coupled tests.
  4. Getter/setter and constructor-only tests.

## Security-Sensitive Test Gaps

- Severity: HIGH.
- Affected paths: Identity, Auth, Token, Access, Security, Tenant, Credential, Session naming patterns.
- Recommended order: fix touched Identity tests during the Identity rewrite before expanding broader suite cleanup.

## Identity-Specific Gaps

- Severity: HIGH for changed Identity work.
- Identity rewrite may proceed only if touched Identity code and tests pass changed-scope gates.

## Tooling False Positives

- Severity: MEDIUM until proven LOW.
- Candidate areas: self-explaining orphan doc examples, generated/reference docs, and broad shallow-test pattern matching.
- Rule: false positives must be narrowed in tooling, not suppressed in baseline without owner/review date.

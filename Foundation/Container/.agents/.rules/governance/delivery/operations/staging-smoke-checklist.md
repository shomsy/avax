# Staging Smoke Checklist

Version: 1.0.0
Status: Normative

Run this after every serious deployment candidate to a stage-like environment.

## Platform Health

- health endpoint or equivalent probe returns healthy
- readiness endpoint or equivalent probe returns ready
- startup logs contain no fatal exception
- trace or correlation identifiers appear in runtime logs
- release or version identity is visible to operators

## Auth And Access Contract

- unauthenticated access is rejected on protected surfaces
- unauthorized role or policy is rejected deterministically
- tenant or scope isolation remains enforced when applicable
- valid credentials still succeed on an allowed route

## Critical Path Verification

- at least one critical read path succeeds
- at least one critical mutation path succeeds
- conflict or duplicate mutation maps to deterministic behavior
- async, queue, or integration path is checked when in scope

## UI Or Runtime Surface

- primary user-facing route or shell loads
- runtime errors are absent on the main path
- protected runtime behavior still works under real auth or scope conditions

## Exit Criteria

- no `high` severity functional failure remains
- no auth, policy, or trust regression remains
- CI or gate result for the release candidate is green

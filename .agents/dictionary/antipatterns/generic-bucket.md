# AntiPattern: Generic Bucket

## What It Is

Code or documentation is grouped under vague categories instead of ownership, behavior, flow, capability, or evidence purpose.

## Symptoms

- Folders or classes named `Utils`, `Helpers`, `Common`, `Base`, `Generic`, `Managers`, or broad `Services`.
- Files contain unrelated responsibilities because the container name has no domain.
- Reviewers cannot infer why a unit belongs where it is.
- New work accumulates in the same vague bucket.

## Why It Is Dangerous

Generic buckets erase architecture signals and make AI agents place new behavior by habit instead of ownership.

## Common AI Failure Mode

The agent creates a broad folder to avoid making a boundary decision.

## How to Fix

Rename by flow, capability, responsibility, or evidence type. Split unrelated content and document any vocabulary exception.

## Allowed Exceptions

Project vocabulary exceptions are allowed only when explicitly documented, scoped, and validated.

## Severity

HIGH for production structure that changes ownership signals. MEDIUM for local documentation or tests.

# Migration Governance

This document defines how to move a repository from one architecture shape to
another without leaving duplicate truth behind.

## Scope

This governance applies to repository shape and architecture movement.
It does not replace coding standards or review rules.

## Required Migration Inputs

Every non-trivial migration should define:

- current boundary and target boundary
- legacy paths that are frozen
- canonical source of truth during each phase
- validation path for every moved capability
- rollback or containment path if the migration fails

## Migration Rules

1. New work lands only in the target shape unless a tracked exception exists.
2. Legacy paths stay readable only while they are actively being retired.
3. There must be one explicit source of truth in every migration phase.
4. Delete compatibility layers as soon as the replacement is validated.
5. If a migration weakens determinism, observability, or rollback posture, pause and redesign it.

## Completion Criteria

- target boundary is fully adopted
- legacy write paths are removed
- validation and recovery notes are documented
- stale references and duplicate contracts are deleted

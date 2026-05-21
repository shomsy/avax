# Access Policy Runtime State Threat Analysis

Date: 2026-05-21

## Asset

Authorization policy decisions and explanations.

## Risk Before

`Policy` used static mutable definitions and evaluator rules. In long-lived workers, one request/test/runtime could register policy state that affected later work.

Unknown named policies implicitly authorized because missing definitions evaluated as an empty rule set.

## Change

Policy definitions and evaluator rules are instance state. Provider registration is scoped.

Unknown named policies now deny by default.

## Fail-Closed Proof Source

Focused test source covers `unknownPolicyNameFailsClosed()` and provider/runtime isolation.

## Residual Yellow

PHPUnit execution remains environment-dependent because PHP tooling is blocked by Docker socket permission denied in this workspace.

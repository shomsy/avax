# Access Policy Runtime State Plan

Date: 2026-05-21

## Scope

- Remove static mutable evaluator and named-policy definitions from `Policy`.
- Keep Access policy state request/runtime scoped.
- Register Policy and PolicyEvaluator through `AccessServiceProvider`.
- Add focused characterization for policy isolation and fail-closed unknown policy names.

## Non-Scope

- Do not redesign the entire Access DSL.
- Do not change AuthBuilder.
- Do not move Access folder structure in this slice.
- Do not remove the legacy callable `AccessPolicy` interface; ownership was already documented in the previous Access cleanup.

## Design Decision

`Policy` becomes an instance capability that receives `PolicyEvaluator` through its constructor. `AccessServiceProvider` owns default assembly.

Unknown named policies now deny by default to avoid fail-open authorization behavior.

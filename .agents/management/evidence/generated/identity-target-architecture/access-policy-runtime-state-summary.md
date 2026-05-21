# Access Policy Runtime State Summary

Date: 2026-05-21

## Changed Files

- `components/Identity/Access/System/Capabilities/Policy/Policy.php`
- `components/Identity/Access/System/Capabilities/Policy/Engine/PolicyEvaluator.php`
- `components/Identity/Access/System/Configuration/AccessServiceProvider.php`
- `tests/Unit/Components/Identity/Access/PolicyCharacterizationTest.php`

## Implementation

`Policy` no longer stores policy definitions or evaluator state in static properties.

Named policy definitions are instance-owned and unknown policy names now fail closed.

`PolicyEvaluator::explain()` now preserves denied-rule status instead of only collecting reason text.

`AccessServiceProvider` registers `PolicyEvaluator` and `Policy` as scoped dependencies.

Focused tests now cover:

- unknown policy name fail-closed behavior
- definition isolation between Policy instances
- rule isolation between Policy instances
- scoped provider isolation
- denied explanation status

## Boundary Result

Access policy state is now runtime scoped and assembled by configuration/provider code.

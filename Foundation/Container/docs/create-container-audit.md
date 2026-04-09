# CreateContainer Audit

`CreateContainer` stays the flow owner. It does not own low-level assembly details.

## Allowed Direct Responsibilities

- normalize `CreateContainerConfig`
- call `AssembleObservability`
- call `AssembleRuntime`
- create the thin `Container` facade
- hand off system seeding to `SeedSystemServices`

## Forbidden Direct Responsibilities

`CreateContainer` should not directly construct or reason about:

- `CompileContainer`
- `ServiceResolver`
- `ServicePool`
- `HotPathInliner`
- `BlueprintCache`

Those responsibilities belong under `src/Capabilities/Composition/Assembly/` and the runtime/compiler owners they wire.

## Proof

The audit is enforced by:

- [`../tests/Flows/CreateContainer/CreateContainerAssemblyAuditSmokeTest.php`](../tests/Flows/CreateContainer/CreateContainerAssemblyAuditSmokeTest.php)

That smoke test asserts the file stays thin, assembly-oriented, and free of direct low-level owner wiring.

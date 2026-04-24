---
title: DataLayer-how-this-works
owner: foundation
last_reviewed: 2026-04-24
classification: internal
---

# DataLayer How This Works

## What this folder is

`Foundation/DataLayer` is the ownership chapter for `Avax\DataLayer`. It exists so the codebase can name this capability
directly instead of hiding it behind a service, manager, helper, or generic runtime bucket.

## Real commands or triggers that reach this folder

- Application code composes the Foundation package through Composer autoloading and calls a public owner such as
  `Avax\DataLayer\DataLayer`.
- Tests under `tests/Foundation` instantiate the same owner classes to verify the boundary and the failure path.

## Exact upstream handoffs

- `vendor/autoload.php`
- function: Composer PSR-4 autoload for `Avax\`
- handoff: application/test code -> `Avax\DataLayer\DataLayer` methods

## The simplest story

- A caller asks for the `DataLayer` capability by name.
- The root owner keeps the capability boundary explicit and delegates only to files in this folder.
- The result is returned as a typed value, explicit failure, or a recorded state/event.

## The first important path

```mermaid
sequenceDiagram
    autonumber
    participant Caller as Application or test code
    participant Owner as DataLayer
    participant File as Direct file in DataLayer
    participant Result as Typed result or explicit failure
    Caller ->> Owner: call capability method
    Owner ->> File: delegate to named responsibility
    File -->> Owner: return value or throw explicit failure
    Owner -->> Result: expose the outcome without hidden global state
```

- **Step 1:** The caller reaches the folder through its root owner, not a generic service locator.
- **Step 2:** The root owner picks the file whose name matches the responsibility.
- **Step 3:** The file returns a typed value or throws a named failure.
- **Step 4:** The caller sees a concrete result or a failure message that names the broken boundary.

## Direct files in this folder

- `DataLayer.php`: documents the `DataLayer` responsibility.

## Child folders in this folder

- `AccelerateDataReads/`: open `docs/Foundation/DataLayer/AccelerateDataReads/how-this-works.md` for the narrower
  capability.
- `AccessPersistentData/`: open `docs/Foundation/DataLayer/AccessPersistentData/how-this-works.md` for the narrower
  capability.
- `CommitDataChanges/`: open `docs/Foundation/DataLayer/CommitDataChanges/how-this-works.md` for the narrower
  capability.
- `ConfigureDataLayer/`: open `docs/Foundation/DataLayer/ConfigureDataLayer/how-this-works.md` for the narrower
  capability.
- `CoordinateDataConsistency/`: open `docs/Foundation/DataLayer/CoordinateDataConsistency/how-this-works.md` for the
  narrower capability.
- `DescribeStorageBehavior/`: open `docs/Foundation/DataLayer/DescribeStorageBehavior/how-this-works.md` for the
  narrower capability.
- `DistributeStoredData/`: open `docs/Foundation/DataLayer/DistributeStoredData/how-this-works.md` for the narrower
  capability.
- `EvolveStoredSchema/`: open `docs/Foundation/DataLayer/EvolveStoredSchema/how-this-works.md` for the narrower
  capability.
- `InspectDataLayer/`: open `docs/Foundation/DataLayer/InspectDataLayer/how-this-works.md` for the narrower capability.
- `OperateDataLayer/`: open `docs/Foundation/DataLayer/OperateDataLayer/how-this-works.md` for the narrower capability.
- `PropagateDataChanges/`: open `docs/Foundation/DataLayer/PropagateDataChanges/how-this-works.md` for the narrower
  capability.
- `ProtectStoredData/`: open `docs/Foundation/DataLayer/ProtectStoredData/how-this-works.md` for the narrower
  capability.
- `QueryStoredData/`: open `docs/Foundation/DataLayer/QueryStoredData/how-this-works.md` for the narrower capability.
- `ShapeStoredData/`: open `docs/Foundation/DataLayer/ShapeStoredData/how-this-works.md` for the narrower capability.

## What gets written or changed

- Runtime code writes nothing by default unless a capability name explicitly says `Record`, `Save`, `Append`, `Write`,
  `Publish`, or `Commit`.
- Tests may create in-memory state to prove the capability boundary.

## Failure path

- Missing runtime dependencies fail before work starts.
- Invalid definitions, unsafe retries, duplicate commands, and unsafe raw queries fail with named exceptions.

## Debug first

- Start in `Foundation/DataLayer/DataLayer.php` when the public entry point is unclear.
- Start in the exact file named by the failing exception when a capability rejects input.

## What to remember

- Folder names are flow or capability names.
- File names are responsibility names.
- Methods name the action that crosses the boundary.

## Dictionary

- `owner`: the file that gives this folder one clear public responsibility.
- `capability`: a named behavior slice that can be composed without a generic services folder.
- `boundary`: the point where dependency, state, or failure rules become explicit.
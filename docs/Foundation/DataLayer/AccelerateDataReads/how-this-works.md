---
title: DataLayer-AccelerateDataReads-how-this-works
owner: foundation
last_reviewed: 2026-04-24
classification: internal
---

# AccelerateDataReads How This Works

## What this folder is

`Foundation/DataLayer/AccelerateDataReads` is the ownership chapter for `Avax\DataLayer\AccelerateDataReads`. It exists
so the codebase can name this capability directly instead of hiding it behind a service, manager, helper, or generic
runtime bucket.

## Real commands or triggers that reach this folder

- Application code composes the Foundation package through Composer autoloading and calls a public owner such as
  `Avax\DataLayer\AccelerateDataReads\AccelerateDataReads`.
- Tests under `tests/Foundation` instantiate the same owner classes to verify the boundary and the failure path.

## Exact upstream handoffs

- `vendor/autoload.php`
- function: Composer PSR-4 autoload for `Avax\`
- handoff: application/test code -> `Avax\DataLayer\AccelerateDataReads\AccelerateDataReads` methods

## The simplest story

- A caller asks for the `AccelerateDataReads` capability by name.
- The root owner keeps the capability boundary explicit and delegates only to files in this folder.
- The result is returned as a typed value, explicit failure, or a recorded state/event.

## The first important path

```mermaid
sequenceDiagram
    autonumber
    participant Caller as Application or test code
    participant Owner as AccelerateDataReads
    participant File as Direct file in AccelerateDataReads
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

- `AccelerateDataReads.php`: documents the `AccelerateDataReads` responsibility.
- `ChooseReadProjection.php`: documents the `ChooseReadProjection` responsibility.
- `ChooseDataIndex.php`: documents the `ChooseDataIndex` responsibility.
- `UseMaterializedView.php`: documents the `UseMaterializedView` responsibility.
- `UseReadCache.php`: documents the `UseReadCache` responsibility.
- `InvalidateReadCache.php`: documents the `InvalidateReadCache` responsibility.
- `UseBloomFilter.php`: documents the `UseBloomFilter` responsibility.
- `DescribeBloomFilter.php`: documents the `DescribeBloomFilter` responsibility.
- `DetectSlowDataQuery.php`: documents the `DetectSlowDataQuery` responsibility.
- `ReadAccelerationPlan.php`: documents the `ReadAccelerationPlan` responsibility.
- `ReadCachePolicy.php`: documents the `ReadCachePolicy` responsibility.
- `MaterializedViewPolicy.php`: documents the `MaterializedViewPolicy` responsibility.
- `BloomFilterPolicy.php`: documents the `BloomFilterPolicy` responsibility.
- `SlowDataQueryReport.php`: documents the `SlowDataQueryReport` responsibility.

## Child folders in this folder

- No child ownership folders exist below this folder.

## What gets written or changed

- Runtime code writes nothing by default unless a capability name explicitly says `Record`, `Save`, `Append`, `Write`,
  `Publish`, or `Commit`.
- Tests may create in-memory state to prove the capability boundary.

## Failure path

- Missing runtime dependencies fail before work starts.
- Invalid definitions, unsafe retries, duplicate commands, and unsafe raw queries fail with named exceptions.

## Debug first

- Start in `Foundation/DataLayer/AccelerateDataReads/AccelerateDataReads.php` when the public entry point is unclear.
- Start in the exact file named by the failing exception when a capability rejects input.

## What to remember

- Folder names are flow or capability names.
- File names are responsibility names.
- Methods name the action that crosses the boundary.

## Dictionary

- `owner`: the file that gives this folder one clear public responsibility.
- `capability`: a named behavior slice that can be composed without a generic services folder.
- `boundary`: the point where dependency, state, or failure rules become explicit.
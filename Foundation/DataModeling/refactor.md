# DataModeling Refactor

## Status

- Root public surfaces normalized: `Arrhae.php`, `Collection.php`
- Contract ownership normalized: `Contracts/CollectionInterface.php`
- Test placement normalized under `tests/Foundation/DataModeling`
- `Collections/Create` slice completed
- Locking behavior repaired for `Arrhae`
- Missing ownership docs completed
- Repo-level docs mirror introduced under `docs/Foundation/DataModeling`

## Goals

- keep root API small and readable
- keep collection behavior feature-sliced by responsibility
- avoid generic helpers/utilities buckets
- keep docs and tests outside the execution path where possible

## Notes

- `Collection::pull()` remains a historical contract edge because the component is otherwise immutable-first.
- Further public API changes should only be made with explicit consumer confirmation.

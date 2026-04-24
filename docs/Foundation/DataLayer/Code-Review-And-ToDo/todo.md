---
title: DataLayer-todo
owner: foundation
last_reviewed: 2026-04-24
classification: internal
---

# DataLayer ToDo

## Milestone 1 Complete

- Foundation/DataLayer exists as a data architecture umbrella, not a replacement for Foundation/Database.
- Foundation/Database remains the concrete database runtime.
- DataLayer reaches Database only through `AccessPersistentData/UseDatabaseRuntime.php`.
- Public facade exposes named capabilities instead of a generic service bucket.
- Docs mirror exists under `docs/Foundation/DataLayer`.

## Next Capabilities

- Expand schema risk detection in `EvolveStoredSchema`.
- Add real database adapter execution behind `ExecuteRawDataQuery`.
- Add transactional outbox storage in `PropagateDataChanges`.
- Add tenant boundary enforcement storage in `ProtectStoredData`.
- Add query fingerprint integration between `QueryStoredData` and `InspectDataLayer`.
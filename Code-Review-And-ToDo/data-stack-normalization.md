# Data Stack Normalization Plan

## Phase 5: Data Stack Normalization

### Current State

| Component      | Path                       | Status                        |
|----------------|----------------------------|-------------------------------|
| Data           | components/Data/           | PARTIAL                       |
| DataFoundation | components/DataFoundation/ | DEPRECATED (REAL BEHAVIOR)    |
| Persistence    | components/Persistence/    | PARTIAL                       |
| DataLayer      | components/DataLayer/      | REMOVED                       |
| Database       | components/Database/       | KEEP (ORM EXTRACTION PENDING) |

---

### Final Ownership

```
components/
  Data/
    System/
      PublicSurface/
      Capabilities/
      Flows/
      Configuration/
      Foundation/
  
  Persistence/
    System/
      PublicSurface/
      Capabilities/
      Flows/
      Configuration/
      Foundation/
  
  Database/
    System/
      PublicSurface/
      Capabilities/
      Flows/
      Configuration/
      Foundation/
```

---

### Rules

1. **Data** owns in-memory data structures and transformations
2. **Database** owns connections, query, transactions, schema, migrations  
3. **Persistence** owns EntityManager, Repository, UnitOfWork, IdentityMap, Mapping, Hydration, ChangeTracking
4. Database must NOT own ORM long-term
5. Data must NOT depend on Database or Persistence
6. Database must NOT depend on Persistence
7. Persistence may depend on Database contracts and Data
8. No duplicate owners: DataFoundation and DataLayer must contain only bridge files (aliases) or be deleted.

---

### Migration Status

| Task                                           | Status      |
|------------------------------------------------|-------------|
| Inventory DataFoundation                       | DONE        |
| Inventory DataLayer                            | DONE        |
| Move DataFoundation files to components/Data   | IN PROGRESS |
| Move DataLayer files to components/Persistence | DONE        |
| Keep compatibility bridges                     | DONE        |
| Add deprecation notes                          | PENDING     |
| ORM extraction from Database                   | PENDING     |

---

### Compatibility Bridges

- `components/DataFoundation/` → alias to `components/Data/`
- `components/DataLayer/` → alias to `components/Persistence/`

These bridges should be removed after all consumers migrate.
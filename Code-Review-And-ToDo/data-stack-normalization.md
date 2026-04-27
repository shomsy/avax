# Data Stack Normalization Plan

## Phase 5: Data Stack Normalization

### Current State

| Component | Path | Status |
|-----------|------|--------|
| Data | components/Data/ | DONE |
| DataFoundation | components/DataFoundation/ | DEPRECATED |
| Persistence | components/Persistence/ | DONE |
| DataLayer | components/DataLayer/ | DEPRECATED |
| Database | components/Database/ | KEEP |

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

---

### Migration Status

| Task | Status |
|------|--------|
| Inventory DataFoundation | DONE |
| Inventory DataLayer | DONE |
| Move DataFoundation files to components/Data | DONE |
| Move DataLayer files to components/Persistence | DONE |
| Keep compatibility bridges | DONE |
| Add deprecation notes | PENDING |

---

### Compatibility Bridges

- `components/DataFoundation/` → alias to `components/Data/`
- `components/DataLayer/` → alias to `components/Persistence/`

These bridges should be removed after all consumers migrate.
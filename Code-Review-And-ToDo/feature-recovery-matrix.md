# Avax Feature Recovery Matrix

*Updated: 2026-04-27 — Post-recovery implementation*

## Legend

| Status     | Meaning                                                     |
|------------|-------------------------------------------------------------|
| ✅ Restored | Feature recovered with proper architecture, ready for tests |
| 🔧 Partial | Feature exists but needs further work                       |
| ⏳ Postpone | Feature planned for later phases                            |
| 🔌 Adapter | Feature should come from external library via adapter       |
| ❌ Dropped  | Intentionally removed, will not return                      |

---

## Priority A: RESTORE NOW (Completed)

| Old Component | Old Feature                                                        | New Owner                | New Location                                               | Status     | Notes                                                    |
|---------------|--------------------------------------------------------------------|--------------------------|------------------------------------------------------------|------------|----------------------------------------------------------|
| Collections   | `map()`, `filter()`, `reduce()`                                    | `components/Data`        | `Capabilities/Collections/Collection.php`                  | ✅ Restored | Were present but minimal                                 |
| Collections   | `groupBy()`, `pluck()`, `flatten()`, `merge()`, `chunk()`          | `components/Data`        | `Capabilities/Collections/Collection.php`                  | ✅ Restored | Fully recovered chainable methods                        |
| Collections   | `sortBy()`, `unique()`, `reverse()`, `take()`, `skip()`            | `components/Data`        | `Capabilities/Collections/Collection.php`                  | ✅ Restored | New immutable implementations                            |
| Collections   | `sum()`, `avg()`, `min()`, `max()`, `contains()`, `firstWhere()`   | `components/Data`        | `Capabilities/Collections/Collection.php`                  | ✅ Restored | Aggregation methods recovered                            |
| Collections   | `toJson()`, `implode()`, `wrap()`, `empty()`                       | `components/Data`        | `Capabilities/Collections/Collection.php`                  | ✅ Restored | Output and factory methods                               |
| Data/Arrays   | Type-safe readers (getInt, getString, getBool, getFloat, getArray) | `components/Data`        | `Capabilities/Arrays/ArrayReader.php`                      | ✅ Restored | NEW: never existed before                                |
| Data/Arrays   | `require()`, `requireNested()`                                     | `components/Data`        | `Capabilities/Arrays/ArrayReader.php`                      | ✅ Restored | NEW: fail-fast accessors                                 |
| Data/Arrays   | `hasNested()`, `dot()`, `undot()`                                  | `components/Data`        | `Capabilities/Arrays/ArrayReader.php`                      | ✅ Restored | Dot-notation array operations                            |
| Persistence   | UnitOfWork (real implementation)                                   | `components/Persistence` | `Capabilities/UnitOfWork/UnitOfWork.php`                   | ✅ Restored | Was skeleton with empty methods                          |
| Persistence   | UnitOfWork diagnostics (`hasPendingChanges`, `pendingSummary`)     | `components/Persistence` | `Capabilities/UnitOfWork/UnitOfWork.php`                   | ✅ Restored | NEW: worker safety diagnostics                           |
| Persistence   | EntityPersisterInterface                                           | `components/Persistence` | `Capabilities/UnitOfWork/EntityPersisterInterface.php`     | ✅ Restored | NEW: clean contract for storage delegation               |
| Persistence   | IdentityMap (full)                                                 | `components/Persistence` | `Capabilities/IdentityMap/IdentityMap.php`                 | ✅ Restored | Moved from Database/ORM, added has/allFor/count          |
| Persistence   | HydratorInterface + ReflectionHydrator                             | `components/Persistence` | `Capabilities/Hydration/`                                  | ✅ Restored | Moved from Database/ORM, added snake→camel mapping       |
| Persistence   | Repository (real implementation)                                   | `components/Persistence` | `Capabilities/Repositories/Repository.php`                 | ✅ Restored | Was skeleton, now has full CRUD + findOneBy/exists/count |
| Persistence   | RepositoryStorageInterface                                         | `components/Persistence` | `Capabilities/Repositories/RepositoryStorageInterface.php` | ✅ Restored | Replaces old RepositoryBackend                           |
| Persistence   | PublicSurface (complete)                                           | `components/Persistence` | `PublicSurface/Persistence.php`                            | ✅ Restored | Wires UoW, Registry, Hydrator, IdentityMap               |
| Data          | PublicSurface (updated)                                            | `components/Data`        | `PublicSurface/Data.php`                                   | ✅ Restored | `collect()` factory instead of singleton                 |

## Priority A: RESTORE NOW (Remaining — Already Existed)

| Old Component | Old Feature                   | New Owner              | New Location                            | Status     | Notes                                 |
|---------------|-------------------------------|------------------------|-----------------------------------------|------------|---------------------------------------|
| Database      | Query Builder (advanced)      | `components/Database`  | `Capabilities/Query/` (12 subdirs)      | 🔧 Partial | Already robust, needs audit           |
| Database      | Transactions + Savepoints     | `components/Database`  | `Capabilities/Transactions/` (9+ files) | 🔧 Partial | Already has deadlock, retry, profiler |
| Database      | Connections + Pools           | `components/Database`  | `Capabilities/Connections/` (7 subdirs) | 🔧 Partial | Already has pool/R-W split            |
| Database      | Schema + Migrations           | `components/Database`  | `Capabilities/Migrations/` (10 subdirs) | 🔧 Partial | Already has full migration system     |
| Container     | Bindings, Resolution, Scopes  | `components/Container` | `System/Capabilities/`                  | 🔧 Partial | Needs metadata audit                  |
| Text          | slug, camel, snake, regex DSL | `components/Text`      | `Text.php` (409 lines)                  | 🔧 Partial | Already solid, 12KB                   |

## Priority B: RESTORE LATER

| Old Component | Old Feature                      | New Owner                 | Status     | Notes                             |
|---------------|----------------------------------|---------------------------|------------|-----------------------------------|
| Console       | Tables, progress bars, questions | `components/Commands`     | ⏳ Postpone | Nice-to-have for DX               |
| View          | Basic internal renderer          | `components/View`         | ⏳ Postpone | ADR-0013 says Blade is adapter    |
| Debug         | Rich error screens               | `components/DumpDebugger` | ⏳ Postpone | ADR says use external tools       |
| Testing       | Fakes (EventFake, CacheFake)     | `components/*/Tests/`     | ⏳ Postpone | Only after base components stable |

## Priority C: ADAPTER / EXTERNAL / POSTPONE

| Old Component | Old Feature          | Decision   | ADR      | Notes                                       |
|---------------|----------------------|------------|----------|---------------------------------------------|
| Carbon        | Date manipulation    | 🔌 Adapter | ADR-0014 | Use PSR-20 ClockInterface + native DateTime |
| BladeOne      | Full template engine | 🔌 Adapter | ADR-0013 | Framework provides simple renderer only     |
| Mail          | SMTP, notifications  | 🔌 Adapter | ADR-0016 | Use Symfony Mailer if needed                |
| Ignition      | Debug error screens  | 🔌 Adapter | —        | External dependency only                    |
| Queues/Jobs   | Async processing     | ⏳ Postpone | ADR-0015 | Worker runtime handles this later           |
| Broadcasting  | WebSockets           | ⏳ Postpone | —        | Not needed for v0                           |
| I18N          | Translations         | ⏳ Postpone | —        | Not needed for v0                           |
| Notifications | Push/SMS/DB          | ⏳ Postpone | —        | Not needed for v0                           |

---

## Dual Ownership Issues (Must Resolve)

| Component                                               | Issue                                                                  | Resolution                                                                                       |
|---------------------------------------------------------|------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------|
| `Database/ORM/UnitOfWork` vs `Persistence/UnitOfWork`   | Both existed, Database had real code, Persistence had skeleton         | **Persistence is now canonical owner.** Database/ORM/UnitOfWork should be deprecated             |
| `Database/ORM/IdentityMap` vs `Persistence/IdentityMap` | Database had real code, Persistence had nothing                        | **Persistence is now canonical owner.** Database copy should delegate or be deprecated           |
| `Database/ORM/Repository` vs `Persistence/Repository`   | Database had 291-line implementation, Persistence had 52-line skeleton | **Persistence is now canonical owner.** Database/ORM/Repository stays for direct-query use cases |
| `Database/ORM/Hydrator` vs `Persistence/Hydration`      | Database had real code, Persistence had nothing                        | **Persistence is now canonical owner**                                                           |

> **Next step:** Deprecate `Database/ORM/UnitOfWork`, `Database/ORM/IdentityMap`, and `Database/ORM/Hydration` to point
> at Persistence equivalents. Keep `Database/ORM/Repository` as a query-oriented repository (different concern from
> Persistence Repository).

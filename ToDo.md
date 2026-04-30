# 📋 ToDo: Avax Feature Recovery Plan

> Taskovi za vraćanje izgubljenih pod-sistema iz `avax.txt` / `avax-backup.txt` u novu Screaming Architecture.
> Svaki task je jedan zatvoreni pod-sistem. Redosled prati prioritet iz `missing-features-after-refactor.md`.

---

## 🔴 P0 — Kritično (framework ne funkcioniše bez ovoga)

### [ ] TASK-001: Session Flow-ovi (Čitanje/Pisanje/Brisanje)
**Komponenta:** `HTTP/Session`
**Izvor:** `avax.txt` linija ~1061007–1061451
**Status:** ⚠️ PARTIALLY IMPLEMENTED — Session komponenta postoji sa osnovnom strukturom. Testovi postoje za
ReadSessionValue, WriteSessionValue, RegenerateSessionId, TerminateSession. Flow-ovi delimično implementirani.
**Šta treba:**

- [ ] Proveriti kompletnost svih Session flow-ova
- [ ] `ClearSession` flow — čišćenje cele sesije
- [ ] `DestroySession` flow — potpuno uništavanje sesije
**Rezultat:** Session komponenta ume da čita, piše, briše i regeneriše.

### [ ] TASK-002: CSRF zaštita
**Komponenta:** `HTTP/Security` (novi folder)
**Izvor:** `avax-backup.txt` linija ~493724–494094
**Status:** ⚠️ PARTIALLY IMPLEMENTED — `components/HTTP/Security/functions.php` postoji. CSRF funkcije su dostupne.
**Šta treba:**

- [ ] Proveriti kompletnost CsrfTokens i VerifyCsrfToken implementacije
**Rezultat:** Forme imaju CSRF zaštitu.

---

## 🟠 P1 — Visok prioritet (core funkcionalnost)

### [ ] TASK-003: Validation Engine
**Komponenta:** `Application/Validation`
**Izvor:** `avax.txt` linija ~1062627–1063126
**Status:** ⚠️ PARTIALLY IMPLEMENTED — Validacioni atributi i rule klase postoje u DataFoundation.
**Šta treba:**

- [ ] Proveriti kompletnost ValidateDto engine i ValidationResult DTO
**Rezultat:** Input validacija radi automatski preko atributa na DTO klasama.

### [ ] TASK-004: Event System (Pub/Sub)
**Komponenta:** `Operations/Events`
**Izvor:** `avax.txt` linija ~988131–988300
**Status:** ⚠️ PARTIALLY IMPLEMENTED — EventDispatcher i ListenerRegistry struktura postoji.
**Šta treba:**

- [ ] Proveriti kompletnost Events PublicSurface i DispatchEvent flow
**Rezultat:** Komponente mogu da emituju i slušaju evente.

### [ ] TASK-005: Logging Writers + Global Error Handler
**Komponenta:** `Operations/Logging`
**Izvor:** `avax.txt` linija ~1053000–1053576
**Status:** ⚠️ PARTIALLY IMPLEMENTED — `components/Operations/Logging/functions.php` postoji.
**Šta treba:**

- [ ] Proveriti kompletnost FileLogWriter, RotatingFileWriter i HandleGlobalError flow
**Rezultat:** Greške se loguju u rotirajuće fajlove. Fatal errors se hvataju.

---

## 🟡 P2 — Srednji prioritet (kompletiranje feature seta)

### [ ] TASK-006: Filesystem Diskovi i Operacije
**Komponenta:** `Application/Filesystem`
**Izvor:** `avax-backup.txt` linija ~438538+ i `avax.txt` ~450322–451872
**Status:** ⚠️ PARTIALLY IMPLEMENTED — Filesystem komponenta postoji sa testovima za file, path i directory operacije.
**Šta treba:**

- [ ] Proveriti kompletnost Disk driver pattern-a
**Rezultat:** Filesystem ume da radi sa fajlovima, folderima i diskovima.

### [x] TASK-007: HTTP Enums + URI Parser
**Komponenta:** `HTTP/Enums` (novi) + `HTTP/URI` (novi)
**Izvor:** `avax-backup.txt` linija ~441346–441598 (Enums) i ~501091–502943 (URI)
**Status:** ✅ COMPLETED — `HttpMethod`, `HttpStatusCode`, `HttpReasonPhrase`, `RequestOption` enum-ovi kreirani. `Uri`
klasa sa parser-om i builder-om implementirana.
**Rezultat:** HTTP sloj koristi tipizirane enum-ove. URL-ovi se parsiraju i grade programatski.

### [ ] TASK-008: HTTP Context
**Komponenta:** `HTTP/Context` (novi)
**Izvor:** `avax-backup.txt` linija ~440799–441112
**Status:** ⚠️ PARTIALLY IMPLEMENTED — `components/HTTP/Context/functions.php` i HttpContext struktura postoje.
**Šta treba:**

- [ ] Proveriti kompletnost mock-ovanja HTTP globala
**Rezultat:** Testovi mogu da mockuju HTTP globale bez `$_SERVER` hakova.

### [ ] TASK-009: View / Blade Normalizacija
**Komponenta:** `Presentation/View`
**Izvor:** `avax.txt` linija ~1063297–1063416
**Status:** ⚠️ PARTIALLY IMPLEMENTED — `components/Presentation/View/functions.php` postoji. BladeOne integration je u
vendor-u.
**Šta treba:**

- [ ] Proveriti kompletnost View PublicSurface i RenderView flow
- [ ] Normalizacija arhitekture prema Screaming Architecture
**Rezultat:** View rendering radi po Screaming Architecture pravilima.

### [x] TASK-010: CLI Console & Code Generators

**Komponenta:** `CLI/Console`
**Izvor:** `avax-backup.txt` linija ~257987–258242 i ~800553–801008
**Status:** ✅ COMPLETED — CLI Console komponenta implementirana u `components/CLI/Console/`.
**Šta je urađeno:**

- [x] `Console` PublicSurface — register(), run(), call()
- [x] `Command` capability — definicija komandi sa argumentima i opcijama
- [x] `CommandRegistry` capability — registar svih komandi
- [x] `CommandInvoker` capability — izvršavanje komandi
- [x] `Input/Output` capabilities — CLI I/O interfejsi
- [x] `ProgressBar`, `Table` UI komponente
- [x] `MakeController`, `MakeEntity`, `MakeRepository`, `MakeService` generator komande
- [x] Generator interfejsi i default stubovi
**Rezultat:** Developer može da koristi `php avax make:controller UserController`.

---

## 🔵 P3 — Nizak prioritet (skalabilnost i convenience)

### [ ] TASK-011: Database Connection Pooling & Migrations
**Komponenta:** `DataStack/Database`
**Izvor:** `avax.txt` linija ~427504–430000+ i `avax-backup.txt`
**Status:** ⚠️ PARTIALLY IMPLEMENTED — Database komponenta postoji sa Connections, Query, EntityManager, Schema,
Migrations, Transactions, Telemetry capability-ima.
**Šta treba:**

- [ ] ConnectionPool sa MySQLPool implementacijom
- [ ] LazyConnectionPool, MultiTenantPool
- [ ] Migration komande: Migrate, Rollback, Seed, Fresh, Status
**Rezultat:** Database podržava pooling, multi-tenancy i migracije.

### [ ] TASK-012: Cache Distribution & Compiled Cache
**Komponenta:** `Application/Cache`
**Izvor:** `avax.txt` linija ~248526–249400+ i ~247413–248500+
**Status:** ⚠️ PARTIALLY IMPLEMENTED — Cache komponenta postoji sa ConsistentHashRing, CacheCluster,
DistributedCacheStore, TieredCache.
**Šta treba:**

- [ ] Proveriti kompletnost CompiledCache sa manifestom i atomičnim pisanjem
- [ ] Proveriti PrimaryReplica read-from-replica
**Rezultat:** Cache radi distribuirano sa replikacijom i kompajliranim PHP fajlovima.

### [x] TASK-013: Facade System
**Komponenta:** `Application/Facade` ili `components/Facade`
**Izvor:** `avax-backup.txt` linija ~438321–438538
**Status:** ✅ COMPLETED — `BaseFacade` sa `__callStatic` proxy ka Container-u implementiran. Fasade za `Auth`,
`Request`, `Route`, `Session`, `Storage` kreirane.
**Rezultat:** Statički pristup servisima: `Auth::user()`, `Route::get()`.

### [x] TASK-014: Security Encryption
**Komponenta:** `Identity/Security`
**Status:** ✅ COMPLETED — AES-256-GCM encryption implementirana u `components/Identity/Security/`.
**Šta je urađeno:**

- [x] `Encrypter` PublicSurface — encrypt(), decrypt(), makeKey()
- [x] `Encryption` capability — AES-256-GCM sa authenticated encryption
- [x] `KeyGenerator` capability — sigurno generisanje ključeva
- [x] `MacGenerator` capability — HMAC verifikacija
- [x] `PayloadSerializer/Deserializer` — base64 + JSON payload format
- [x] Testovi: 41 test, 69 assertions (EncryptionTest.php)
  **Rezultat:** Framework može da šifruje osetljive podatke sa AES-256-GCM.

### [ ] TASK-015: HTTP Client (Outbound)
**Komponenta:** `HTTP/Client` (novi)
**Status:** ⚠️ PARTIALLY IMPLEMENTED — Guzzle HTTP client je u vendor dependencies.
**Šta treba:**

- [ ] Kreirati Avax wrapper komponentu za Guzzle
**Rezultat:** Framework može da komunicira sa eksternim API-jima.

---

## ⚪ P4 — Enterprise (odloženo)

### [x] TASK-016: Saga / ApplicationWorkflow

**Komponenta:** `Operations/ApplicationWorkflow`
**Izvor:** `avax.txt` linija ~36087–40000+
**Status:** ✅ COMPLETED — Saga/Workflow implementirana u `components/Operations/ApplicationWorkflow/`.
**Šta je urađeno:**

- [x] `Saga` PublicSurface — static DSL: define(), step(), execute(), compensate()
- [x] `SagaStep` — koraci sa akcijama i kompenzacijama
- [x] `CompensationExecutor` — automatski rollback u reverse order
- [x] `StepRunner` — izvršavanje koraka sa idempotency
- [x] `IdempotencyKey` — zaštita od duplih izvršavanja
- [x] `SagaState` enum — Running, Completed, Failed, Compensating, Compensated
- [x] `SagaStore` / `InMemorySagaStore` — perzistencija saga stanja
- [x] `SagaResult` — rezultat izvršavanja sa step results
- [x] Testovi: 35 testova, 90 assertiona (SagaTest.php)
**Rezultat:** Orkestacija višekoračnih poslovnih procesa sa automatskim rollback-om.

### [x] TASK-017: DataLayer Advanced (QueryIntent, N+1 Detection)

**Komponenta:** Pod `DataStack`
**Izvor:** `avax.txt` linija ~391885–396000+
**Status:** ✅ COMPLETED — QueryIntent i N+1 detection implementirani u `components/DataStack/`.
**Šta je urađeno:**

- [x] `QueryIntent` — intent-based query building sa fluent interfejsom
- [x] N+1 query detection — `NPlusOneDetector` sa threshold alarmiranjem
- [x] `QueryPattern` — prepoznavanje pattern-a u upitima
- [x] `SelectIntent`, `InsertIntent`, `UpdateIntent`, `DeleteIntent` — tipizirani intent-i
- [x] `QueryBuilder` — fluent query builder
- [x] `WhereClause`, `OrderBy`, `JoinClause` — query komponente
- [x] Testovi: 70 testova, 136 assertiona (QueryIntentTest.php)
  **Napomena:** Bloom Filter, 2PC, i Deadlock detection nisu implementirani — zahtevaju database-level podršku.
  **Rezultat:** Enterprise-grade query building sa N+1 detekcijom.

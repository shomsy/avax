# 📋 ToDo: Avax Feature Recovery Plan

> Taskovi za vraćanje izgubljenih pod-sistema iz `avax.txt` / `avax-backup.txt` u novu Screaming Architecture.
> Svaki task je jedan zatvoreni pod-sistem. Redosled prati prioritet iz `missing-features-after-refactor.md`.

---

## 🔴 P0 — Kritično (framework ne funkcioniše bez ovoga)

### [ ] TASK-001: Session Flow-ovi (Čitanje/Pisanje/Brisanje)
**Komponenta:** `HTTP/Session`
**Izvor:** `avax.txt` linija ~1061007–1061451
**Status:** ⚠️ PARTIALLY IMPLEMENTED — Session komponenta postoji sa osnovnom strukturom. Testovi postoje za
ReadSessionValue, WriteSessionValue, RegenerateSessionId, TerminateSession. Potrebno proveriti kompletnost flow-ova.
**Šta treba:**
- [ ] `ReadSessionValue` flow — čitanje vrednosti po ključu
- [ ] `StoreSessionValue` flow — upisivanje vrednosti
- [ ] `ForgetSessionValue` flow — brisanje ključa
- [ ] `ClearSession` flow — čišćenje cele sesije
- [ ] `DestroySession` flow — potpuno uništavanje sesije
- [ ] `RegenerateSession` flow — rotacija session ID-a
**Rezultat:** Session komponenta ume da čita, piše, briše i regeneriše.

### [ ] TASK-002: CSRF zaštita
**Komponenta:** `HTTP/Security` (novi folder)
**Izvor:** `avax-backup.txt` linija ~493724–494094
**Status:** ⚠️ PARTIALLY IMPLEMENTED — `components/HTTP/Security/functions.php` postoji. Potrebno proveriti kompletnost
CsrfTokens i VerifyCsrfToken implementacije.
**Šta treba:**
- [ ] `CsrfTokens` — generisanje i čuvanje CSRF tokena
- [ ] `VerifyCsrfToken` — verifikacija tokena iz forme/headera
- [ ] `csrf_token()` helper funkcija
**Rezultat:** Forme imaju CSRF zaštitu.

---

## 🟠 P1 — Visok prioritet (core funkcionalnost)

### [ ] TASK-003: Validation Engine
**Komponenta:** `Application/Validation`
**Izvor:** `avax.txt` linija ~1062627–1063126
**Status:** ⚠️ PARTIALLY IMPLEMENTED — Validacioni atributi i rule klase postoje u DataFoundation. Potrebno proveriti
ValidateDto engine i ValidationResult DTO.
**Šta treba:**
- [ ] `ValidateDto` — engine koji čita PHP atribute i primenjuje pravila
- [ ] `ValidationResult` — DTO za greške
- [ ] `ValidateInput` flow
- [ ] Atributi: `#[Required]`, `#[Email]`, `#[MinLength]`, `#[Min]`, `#[PasswordComplexity]`
- [ ] Rule klase: `EmailRule`, `MinRule`, `MinLengthRule`, `IntegerRule`
**Rezultat:** Input validacija radi automatski preko atributa na DTO klasama.

### [ ] TASK-004: Event System (Pub/Sub)
**Komponenta:** `Operations/Events`
**Izvor:** `avax.txt` linija ~988131–988300
**Status:** ⚠️ PARTIALLY IMPLEMENTED — EventDispatcher i ListenerRegistry struktura postoji. Potrebno proveriti
kompletnost Events PublicSurface i DispatchEvent flow.
**Šta treba:**
- [ ] `EventDispatcher` capability — dispatch eventa sa propagation stop
- [ ] `ListenerRegistry` capability — registracija listenera sa prioritetima
- [ ] `Events` PublicSurface — listen(), dispatch(), flush()
- [ ] `DispatchEvent` flow
**Rezultat:** Komponente mogu da emituju i slušaju evente.

### [ ] TASK-005: Logging Writers + Global Error Handler
**Komponenta:** `Operations/Logging`
**Izvor:** `avax.txt` linija ~1053000–1053576
**Status:** ⚠️ PARTIALLY IMPLEMENTED — `components/Operations/Logging/functions.php` postoji. Potrebno proveriti
FileLogWriter, RotatingFileWriter i HandleGlobalError flow.
**Šta treba:**
- [ ] `FileLogWriter` — pisanje u fajl sa fallback-om
- [ ] `RotatingFileWriter` — dnevna rotacija sa retencijom (max 30 dana)
- [ ] `HandleGlobalError` flow — hvatanje uncaught exception-a
- [ ] `ErrorLogger` — strukturirano logovanje grešaka
- [ ] `logger()` helper funkcija
**Rezultat:** Greške se loguju u rotirajuće fajlove. Fatal errors se hvataju.

---

## 🟡 P2 — Srednji prioritet (kompletiranje feature seta)

### [ ] TASK-006: Filesystem Diskovi i Operacije
**Komponenta:** `Application/Filesystem`
**Izvor:** `avax-backup.txt` linija ~438538+ i `avax.txt` ~450322–451872
**Status:** ⚠️ PARTIALLY IMPLEMENTED — Filesystem komponenta postoji sa testovima za file, path i directory operacije.
Potrebno proveriti kompletnost Disk driver pattern-a.
**Šta treba:**
- [ ] `Disk` / `DiskDefinition` / `ResolveDisk` — driver pattern
- [ ] `LocalDisk` implementacija
- [ ] File operacije: `ReadFile`, `WriteFile`, `CopyFile`, `MoveFile`, `DeleteFile`, `AppendToFile`
- [ ] Directory operacije: `CreateDirectory`, `ClearDirectory`, `DeleteDirectory`, `ListDirectoryFiles`, `EnsureDirectoryExists`
- [ ] Path operacije: `PathExists`, `PathIsWritable`, `ChangePathPermissions`
- [ ] Failure DTO-ovi za svaku operaciju
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
**Status:** ⚠️ PARTIALLY IMPLEMENTED — `components/HTTP/Context/functions.php` postoji. HttpContext struktura je
kreirana.
**Šta treba:**
- [ ] `HttpContext` — čist pristup `$_SERVER`, `$_GET`, `$_POST`, `$_COOKIE`, `$_FILES`
- [ ] `HttpContextInterface` — za testabilnost (mock-ovanje)
- [ ] `PhpGlobalsProvider` — konkretna implementacija
- [ ] `GlobalsProviderInterface`
**Rezultat:** Testovi mogu da mockuju HTTP globale bez `$_SERVER` hakova.

### [ ] TASK-009: View / Blade Normalizacija
**Komponenta:** `Presentation/View`
**Izvor:** `avax.txt` linija ~1063297–1063416
**Status:** ⚠️ PARTIALLY IMPLEMENTED — `components/Presentation/View/functions.php` postoji. BladeOne integration je u
vendor-u. Potrebno proveriti View PublicSurface i RenderView flow.
**Šta treba:**
- [ ] Premestiti `BladeTemplateEngine.php` u `System/Capabilities/Engines/`
- [ ] Premestiti `TemplateEngine.php` u `System/Capabilities/Engines/`
- [ ] Kreirati `View` PublicSurface (render, exists, share)
- [ ] Kreirati `RenderView` flow
- [ ] `ViewInterface`
**Rezultat:** View rendering radi po Screaming Architecture pravilima.

### [ ] TASK-010: CLI Console & Code Generators
**Komponenta:** `Commands` (novi Suite ili pod `Application`)
**Izvor:** `avax-backup.txt` linija ~257987–258242 i ~800553–801008
**Status:** ❌ NOT IMPLEMENTED — Nema CLI Console komponente u novoj arhitekturi.
**Šta treba:**
- [ ] `CommandDefinitions` — registar komandi
- [ ] `MakeControllerCommand`, `MakeEntityCommand`, `MakeRepositoryCommand`, `MakeServiceCommand`
- [ ] Generator interfejsi (ControllerGenerator, EntityGenerator, itd.)
- [ ] CLI UI: `ProgressBar`, `Table`
**Rezultat:** Developer može da koristi `php avax make:controller UserController`.

---

## 🔵 P3 — Nizak prioritet (skalabilnost i convenience)

### [ ] TASK-011: Database Connection Pooling & Migrations
**Komponenta:** `DataStack/Database`
**Izvor:** `avax.txt` linija ~427504–430000+ i `avax-backup.txt`
**Status:** ⚠️ PARTIALLY IMPLEMENTED — `components/DataStack/Database/functions.php` postoji. Pool struktura je
delimično implementirana.
**Šta treba:**
- [ ] `ConnectionPool` sa MySQLPool implementacijom
- [ ] `LazyConnectionPool` — lazy open
- [ ] `MultiTenantPool` — tenant-aware routing
- [ ] `Schema` builder
- [ ] Migration komande: Migrate, Rollback, Seed, Fresh, Status
**Rezultat:** Database podržava pooling, multi-tenancy i migracije.

### [ ] TASK-012: Cache Distribution & Compiled Cache
**Komponenta:** `Application/Cache`
**Izvor:** `avax.txt` linija ~248526–249400+ i ~247413–248500+
**Status:** ⚠️ PARTIALLY IMPLEMENTED — Cache komponenta postoji sa ConsistentHashRing i CompiledCache strukturom.
**Šta treba:**
- [ ] `ConsistentHashRing` — distribucija na klaster
- [ ] `CacheNode`, `CachePartition`, `RouteCacheRead/Write`
- [ ] `PrimaryReplica` — read-from-replica
- [ ] `CompiledCache` sa manifestom i atomičnim pisanjem
**Rezultat:** Cache radi distribuirano sa replikacijom i kompajliranim PHP fajlovima.

### [x] TASK-013: Facade System
**Komponenta:** `Application/Facade` ili `components/Facade`
**Izvor:** `avax-backup.txt` linija ~438321–438538
**Status:** ✅ COMPLETED — `BaseFacade` sa `__callStatic` proxy ka Container-u implementiran. Fasade za `Auth`,
`Request`, `Route`, `Session`, `Storage` kreirane.
**Rezultat:** Statički pristup servisima: `Auth::user()`, `Route::get()`.

### [ ] TASK-014: Security Encryption
**Komponenta:** `Identity/Security`
**Status:** ❌ NOT IMPLEMENTED — Nema Security Encryption komponente u novoj arhitekturi.
**Šta treba:**
- [ ] Implementacija `EncrypterInterface` sa AES-256
- [ ] Encrypt/Decrypt metode
- [ ] Key rotation podrška
**Rezultat:** Framework može da šifruje osetljive podatke.

### [ ] TASK-015: HTTP Client (Outbound)
**Komponenta:** `HTTP/Client` (novi)
**Status:** ⚠️ PARTIALLY IMPLEMENTED — Guzzle HTTP client je u vendor dependencies. Potrebno kreirati Avax wrapper
komponentu.
**Šta treba:**
- [ ] HTTP Client za slanje GET/POST/PUT/DELETE zahteva
- [ ] Podrška za headers, body, timeout
- [ ] Response parsing
**Rezultat:** Framework može da komunicira sa eksternim API-jima.

---

## ⚪ P4 — Enterprise (odloženo)

### [ ] TASK-016: Saga / ApplicationWorkflow
**Komponenta:** `ApplicationWorkflow`
**Izvor:** `avax.txt` linija ~36087–40000+
**Status:** ❌ NOT IMPLEMENTED — Nema Saga/Workflow komponente u novoj arhitekturi.
**Šta treba:**
- [ ] Saga Definition, Step, Compensation
- [ ] Saga Runtime (MessageBus, StepRunner, SagaStore)
- [ ] Idempotency zaštita
- [ ] Inspekcija (Timeline, Report, EventTracing)
- [ ] Resume mehanizam
**Rezultat:** Orkestacija višekoračnih poslovnih procesa sa automatskim rollback-om.

### [ ] TASK-017: DataLayer Advanced (Bloom, 2PC, Slow Query)
**Komponenta:** Pod `DataStack` ili zaseban modul
**Izvor:** `avax.txt` linija ~391885–396000+
**Status:** ❌ NOT IMPLEMENTED — Enterprise data access optimizacije nisu implementirane.
**Šta treba:**
- [ ] Bloom Filter policy za read acceleration
- [ ] Two-Phase Commit koordinacija
- [ ] Deadlock detekcija
- [ ] Slow query detection i reporting
- [ ] Isolation level management
**Rezultat:** Enterprise-grade data access sa naprednim optimizacijama.

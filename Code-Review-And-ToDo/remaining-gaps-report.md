# Remaining Gaps Report — What's Still Missing for Full Power

*Scan date: 2026-04-27*

## Kritični problemi (🔴 Prazni skeleton-i koji blokiraju rad)

### 1. Container — PRAZAN

- `Capabilities/Bindings/` → **PRAZAN folder**
- `Capabilities/Resolution/` → **PRAZAN folder**
- `Capabilities/Scopes/` → **PRAZAN folder**
- **Impact:** DI container nema implementaciju bind/resolve logike unutar Screaming Architecture strukture. Verovatno
  postoji stara implementacija negde van System/ strukture.
- **Action:** Implementirati Bindings (bind, singleton, instance), Resolution (autowiring, reflection resolver), i
  Scopes (request scope, singleton scope).

### 2. Middleware Pipeline — SKELETON (22 linije!)

- `Middleware/System/Capabilities/Pipeline/MiddlewarePipeline.php` ima **22 linije**
- `execute()` metoda **doslovno vraća request bez obrade** — ne prolazi kroz middleware lanac
- **Contrast:** `HTTP/Middleware/` folder ima **19 fajlova** (CORS, CSRF, RateLimiter, Tracing, Logger...) koji nemaju
  pipeline koji ih izvršava!
- **Impact:** Middleware postoje ali se nikada ne pozivaju sekvencijalno.
- **Action:** Implementirati pravi pipeline sa `handle()` → `next()` delegacijom.

### 3. Events/ListenerRegistry — PRAZAN

- `Events/System/Capabilities/ListenerRegistry/` → **PRAZAN folder**
- **Impact:** Event sistem nema mogućnost registrovanja i pozivanja listenera.
- **Action:** Implementirati ListenerRegistry, EventDispatcher, i listener resolution.

### 4. Config/Repository — SKELETON (20 linija!)

- `ConfigurationRepository.php` ima samo `get()` i `set()` — **nema dot-notation**, nema učitavanje iz fajlova, nema
  merge, nema environment override.
- **Impact:** Konfiguracija je primitivna. Stari sistem je imao bogat config sloj.
- **Action:** Dodati dot-notation čitanje, file loading, env override, has(), all(), merge().

---

## Ozbiljni problemi (🟡 Postoje ali su značajno slabiji)

### 5. DataFoundation/Arrhae — NE KORISTI SE

- `DataFoundation/Arrhae.php` je **627 linija** čistog, moćnog koda (whereIn, whereBetween, whereNull, groupBy, countBy,
  keyBy, pluck, union, diff, intersect, toXml, lock/immutability, pull, tap, when/unless...)
- **ALI:** namespace je `components\DataFoundation\Arrhae` — stari namespace, nije integrisan u novu `Data` komponentu.
- **Impact:** Ogromna snaga postoji u repou ali se ne koristi jer je u legacy namespace-u.
- **Action:** Migrirati Arrhae feature-e u `Data/System/Capabilities/` ili napraviti compatibility bridge.

### 6. DataFoundation/Collection — DUPLIKAT

- `DataFoundation/Collection.php` (12.5KB) je starija, bogatija kolekcija sa starim namespace-om
- `Data/System/Capabilities/Collections/Collection.php` je nova, ojačana verzija
- **Impact:** Dva vlasnika za istu stvar (kršenje ADR-0012 principa). DataFoundation verzija ima neke metode koje nova
  nema (npr. `whereIn`, `whereBetween`, `whenNull`, `countBy`, `tap`, `when/unless`, `toXml`).
- **Action:** Preneti nedostajuće metode u novu Collection, pa deprecirati DataFoundation.

### 7. View — Bazična

- `View/TemplateEngine.php` (9.5KB) i `View/BladeTemplateEngine.php` (5KB) postoje
- Ali `View/System/Capabilities/` treba proveriti da li ima prave strukture
- **Action:** Audit View za System conformance.

### 8. Session/Security i Session/Storage — Nepoznat sadržaj

- Postoje folderi ali treba utvrditi koliko su implementirani
- **Action:** Deep scan sadržaja.

### 9. Logging — Van System/ strukture

- `Logging/` ima 9 PHP fajlova (ErrorHandler, ErrorLogger, FileLogWriter, LoggerFactory...) ali su **van System/**
  strukture
- **Impact:** Logging ne prati Screaming Architecture.
- **Action:** Refaktor u System/Capabilities/PublicSurface per refactor.md.

---

## Manji problemi (🟢 Funkcionalno ali ne prati novu arhitekturu)

### 10. Text — Nema System/ strukturu

- `Text/Text.php` (409 linija) je moćan ali živi van `System/` okvira
- **Action:** Premestiti u `Text/System/Capabilities/` ili bar kreirati System/PublicSurface wrapper.

### 11. DumpDebugger — Minimalan

- Samo 2 fajla (`DumpDebugger.php` + `functions.php`)
- **Action:** OK za sada, ali treba dodati output formatere (HTML, CLI, JSON).

### 12. ApplicationWorkflow — Nepoznat status

- Treba proveriti da li je integrisan u framework/System/Flows

### 13. DataFoundation — Cela komponenta treba odluku

- 13 poddirektorijuma: Collections, Composites, Contracts, DataTransfer, Exceptions, Flows, Internal, Interop,
  ObjectHandling, Structures, Validation, Values
- **Ovo je kompletna stara data biblioteka** koja postoji paralelno sa novom `Data` komponentom
- **Action:** Feature-po-feature migracija u `Data`, zatim brisanje DataFoundation.

---

## Metrički pregled

| Komponenta     | Fajlova                                | Status           | Snaga                                      |
|----------------|----------------------------------------|------------------|--------------------------------------------|
| Auth           | 2 velika + 6 Capabilities dirs         | 🟢 Solidna       | Auth.php=20KB, DefaultAuth=33KB            |
| Cache          | 6 fajlova + 6 Capabilities dirs        | 🟢 Solidna       | AvaxCache=13KB, CompiledCache, CacheResult |
| Container      | **3 PRAZNA foldera**                   | 🔴 Skeleton      | Nema bind/resolve/scopes                   |
| Config         | **1 skeleton fajl (20 linija)**        | 🔴 Skeleton      | Samo get/set                               |
| Data           | 5 fajlova (ojačano)                    | 🟢 Ojačana       | Collection=310L, ArrayReader=200L          |
| DataFoundation | **17+ fajlova, 13 dirs**               | 🟡 Legacy        | Arrhae=627L! Treba migrirati               |
| Database       | Bogat (ORM, Query, Tx, Migrations)     | 🟢 Moćna         | Najjača komponenta                         |
| Events         | **1 PRAZAN folder**                    | 🔴 Skeleton      | Nema event dispatch                        |
| Filesystem     | 4 fajla + 5 dirs                       | 🟡 Partial       | Ima Disks, Paths, Directories              |
| HTTP           | **19 middleware + Router + Request**   | 🟢 Moćna         | Najobimnija                                |
| Logging        | 9 fajlova                              | 🟡 Van strukture | Funkcionalno ali ne System/                |
| Middleware     | **1 skeleton fajl (22 linije)**        | 🔴 Skeleton      | Pipeline ne radi!                          |
| Persistence    | 12 fajlova (ojačano)                   | 🟢 Ojačana       | UoW, IdentityMap, Hydrator                 |
| Security       | 2 dirs (Encryption + System)           | 🟡 Partial       | Treba audit                                |
| Session        | 2 dirs (Security + Storage)            | 🟡 Partial       | Treba audit                                |
| Text           | 4 fajla                                | 🟢 Solidna       | Text.php=409L                              |
| Validation     | 3 dirs (Execution, Metadata, Standard) | 🟡 Partial       | Treba audit za pravila                     |
| View           | 3 fajla + System                       | 🟡 Partial       | TemplateEngine=9.5KB                       |

---

## Prioritetni redosled za recovery

1. **🔴 Container** — Bez DI-ja framework ne radi. Bindings + Resolution + Scopes.
2. **🔴 Middleware Pipeline** — 19 middleware-a čeka pipeline koji radi.
3. **🔴 Events** — ListenerRegistry + EventDispatcher.
4. **🔴 Config** — Dot-notation, file loading, env override.
5. **🟡 DataFoundation → Data** — Migrirati Arrhae + missing Collection methods.
6. **🟡 Logging** — Refaktor u System/ strukturu.
7. **🟡 Validation** — Audit standard rules.
8. **🟡 Session** — Audit security + storage.

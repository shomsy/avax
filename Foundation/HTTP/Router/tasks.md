Savršeno. Evo kompletno spreman **Router-Enterprise-Refactor-Plan.md** — sve findings, svi zadaci, svi koraci, svi acceptance kriterijumi, i checkbox za svakog “malog” da označi rešeno ✅

Ovaj fajl možeš mu direktno dati za egzekuciju — čista, produkcijska ToDo arhitekturna mapa:

---

# 🏗️ **HTTP FOUNDATION ROUTER — ENTERPRISE REFACTOR PLAN v3.0**

> **Cilj:** Postići *10/10 enterprise-grade stabilnost, sigurnost i predvidivost* Router komponente.
> Svaki zadatak ima **tačno rešenje**, **datoteke za izmene**, i **acceptance kriterijum**.
> ✅ = rešeno | ☐ = još u toku

---

## 🟥 **CRITICAL — ARCHITECTURE / SECURITY / DETERMINISM**

---

### ✅ **C1. RouteCollector → RouteRegistry migracija**

**Opis:** Ukloniti `static` RouteCollector i potpuno prebaciti registraciju ruta na instance-based `RouteRegistry`.

* 🔧 *Fajlovi:* `RouterDsl.php`, `RouteBootstrapper.php`, `RouteRegistry.php`

* 🧠 *Rešenje:*
  
  * Sve DSL operacije (`get/post/any/group`) koriste `$this->registry`.
  * `RouteCollector` označiti kao `@deprecated`.

* ✅ *Acceptance:*
  
  * Nijedan `static` property u Routeru.
  * Test: `RouteIsolationTest` prolazi bez race condition-a.

---

### ✅ **C2. Route Deduplication Guard**

**Opis:** Sprečiti registraciju duplikata (method + domain + path + name).

* 🔧 *Fajlovi:* `HttpRequestRouter.php`, `DuplicateRouteException.php`

* 🧠 *Rešenje:*
  
  ```php
  $key = "{$route->method->value}|{$route->domain}|{$route->path}";
  if (isset($this->registry[$key])) throw new DuplicateRouteException(...);
  ```

* ✅ *Acceptance:*
  
  * Test: `DuplicateRouteTest` → “Duplicate route detected.”
  * Routes se ne dupliraju ni u cache-u.

---

### ✅ **C3. RouteCacheLoader bez `require`**

**Opis:** Eliminisati `require` kod učitavanja keš fajlova radi sigurnosti.

* 🔧 *Fajlovi:* `RouteCacheLoader.php`, `RouteCacheWriter.php`

* 🧠 *Rešenje:*
  
  * Cache format: JSON + SHA256 signature.
  * Verifikacija pre učitavanja.

* ✅ *Acceptance:*
  
  * Loader više ne koristi `require` ni `eval`.
  * Cache signature validacija prolazi.

---

### ✅ **C4. RouteGroupStack instance-based**

**Opis:** Ukloniti globalno stanje — statički stack.

* 🔧 *Fajlovi:* `RouteGroupStack.php`, `RouterDsl.php`

* 🧠 *Rešenje:*
  
  * `RouteGroupStack` → instance.
  * Injektovati u DSL i Registrar.

* ✅ *Acceptance:*
  
  * Test: `RouteGroupIsolationTest` prolazi.
  * Dva routera ne dele group state.

---

### ✅ **C5. Deterministički 404/405**

**Opis:** Route matcher mora prepoznati pattern route pri 405 odgovoru.

* 🔧 *Fajlovi:* `HttpRequestRouter.php`, `DomainAwareMatcher.php`

* 🧠 *Rešenje:*
  
  * Dodaj `matchesIgnoringMethod()`.
  * Koristi ga u `resolve()`.

* ✅ *Acceptance:*
  
  * `/users/{id}` vraća 405 ako POST ne postoji, 404 ako path ne postoji.

---

### ✅ **C6. Route params izolacija**

**Opis:** Izolovati route parametre u `route.params` atributu.

* 🔧 *Fajlovi:* `RouterKernel.php`, `RouteRequestInjector.php`

* 🧠 *Rešenje:*
  
  ```php
  $request = $request->withAttribute('route.params', $params);
  ```

* ✅ *Acceptance:*
  
  * `request->getAttribute('route.params')` postoji i ne preklapa user atribute.

---

## 🟧 **HIGH — BEHAVIOR / STABILITY**

---

### ✅ **H1. Path Normalization**

* 🔧 *Fajlovi:* `RouteDefinition.php`, `PathNormalizer.php`

* 🧠 *Rešenje:*
  
  ```php
  $path = '/' . trim(preg_replace('#/+#', '/', $path), '/');
  ```

* ✅ *Acceptance:* `/users` == `/users/`

---

### ✅ **H2. Jedinstven FallbackManager**

* 🔧 *Fajlovi:* `FallbackManager.php`, `RouterKernel.php`, `RouterDsl.php`

* 🧠 *Rešenje:*
  
  * Sve fallback registracije idu kroz `FallbackManager`.

* ✅ *Acceptance:* Samo jedan fallback mehanizam, test “fallback routes once” prolazi.

---

### ✅ **H3. Middleware Validation**

* 🔧 *Fajlovi:* `RoutePipeline.php`, `StageChain.php`

* 🧠 *Rešenje:*
  
  * Middleware mora implementirati `RouteMiddleware`.

* ✅ *Acceptance:* Ako middleware ne implementira, boot baca exception.

---

### ✅ **H4. Domain-Aware Routing**

* 🔧 *Fajlovi:* `HttpRequestRouter.php`, `DomainAwareMatcher.php`

* 🧠 *Rešenje:*
  
  * Svaki path ima listu ruta (`[$method][$path][]`).
  * Domain se proverava regexom.

* ✅ *Acceptance:* `/login` radi različito na `api.` i `admin.` domenima.

---

### ✅ **H5. Specificity Sorting**

* 🔧 *Fajlovi:* `HttpRequestRouter.php`, `RouteDefinition.php`

* 🧠 *Rešenje:*
  
  ```php
  $route->specificity = substr_count($route->path, '/') - substr_count($route->path, '{');
  usort($routes, fn($a, $b) => $b->specificity <=> $a->specificity);
  ```

* ✅ *Acceptance:* `/users/me` > `/users/{id}`

---

## 🟨 **MEDIUM — PERFORMANCE / DX / MAINTAINABILITY**

---

### ✅ **M1. Regex Precompilation**

* 🔧 *Fajlovi:* `RouteDefinition.php`, `RouteCacheCompiler.php`
* 🧠 *Rešenje:* Kompajlirati regex i čuvati u `compiledPathRegex`.
* ✅ *Acceptance:* `preg_match` koristi keširani regex.

---

### ✅ **M2. Static Analysis & Linting**

* 🔧 *Fajlovi:* `phpstan.neon`, `.php-cs-fixer.dist.php`
* 🧠 *Rešenje:* Level 8 PHPStan, PSR-12 formatting, CI komanda `composer analyse`.
* ✅ *Acceptance:* Linter + analyser bez grešaka.

---

### ✅ **M3. RouterTrace**

* 🔧 *Fajlovi:* `RouterTrace.php`, `RouterKernel.php`
* 🧠 *Rešenje:* Event hooks `onResolveStart`, `onRouteMatched`, `onFallback`.
* ✅ *Acceptance:* Log prikazuje rutu, trajanje i ishod.

---

### ✅ **M4. Cache Signature Verification**

* 🔧 *Fajlovi:* `RouteCacheLoader.php`, `RouteCacheManifest.php`
* 🧠 *Rešenje:* Validacija SHA256 pre hydrate.
* ✅ *Acceptance:* Korumpiran cache baca `InvalidCacheException`.

---

## 🟩 **LOW — DOCUMENTATION / STANDARDIZATION**

---

### ✅ **L1. PHPDoc & Type Hints**

* 🔧 *Fajlovi:* svi `Routing/*.php`, `Router*.php`
* 🧠 *Rešenje:* Uvesti `@phpstan-type RoutesMap` i eksplicitne povratne tipove.
* ✅ *Acceptance:* 0 PHPStan tip grešaka.

---

### ✅ **L2. Comment Policy**

* 🔧 *Fajlovi:* ceo `Router/`
* 🧠 *Rešenje:* Samo “why” komentari, ne “what”.
* ✅ *Acceptance:* 90% komentara objašnjava dizajn, ne sintaksu.

---

## ⚙️ **EXTRA — TOOLING / QUALITY GATES**

---

### ✅ **E1. Architectural Guard Tests**

* 🔧 *Fajlovi:* `tests/ArchitectureTest.php`

* 🧠 *Rešenje:* Reflection proverava da nema:
  
  * `static` mutable property
  * zavisnost Router→Bootstrap

* ✅ *Acceptance:* Test prolazi 100%.

---

### ✅ **E2. Integration Stability Tests**

* 🔧 *Fajlovi:* `tests/RouterIntegrationTest.php`

* 🧠 *Rešenje:*

  * Compare `compiledCache` vs `runtimeRoutes`.
  * Broj ruta identičan kroz bootstrap faze.

* ✅ *Acceptance:* Deterministični output.

---

## 🏢 **ENTERPRISE EXTENSIONS (ARCHITECTURE CONSOLIDATION)**

---

### ✅ **X1. Kanonska Router arhitektura**

**Opis:** Ujednačiti `HttpRequestRouter` i `RouteCollection` strukturu — jedan canonical source-of-truth.

* 🔧 *Fajlovi:* `HttpRequestRouter.php`, `RouteCollection.php`, `RouteSourceLoaderInterface.php`

* 🧠 *Rešenje:*

  * `RouteCollection` sa mapom `[$method][$path]` za exact, i listom za patterns.
  * `RouteSourceLoaderInterface` → `CachedRouteLoader` i `DiskRouteLoader`.
  * `RouteBootstrapper` jedini orchestration sloj.
  * Ukloniti "mrtve" fajlove (stari loaderi).

* ✅ *Acceptance:* Jedna implementacija bez paralelnih varijanti.

---

### ✅ **X2. Route Key determinism**

**Opis:** Dodati `RouteKey` value object i dedupe guard sa konfigurabilnom politikom.

* 🔧 *Fajlovi:* `RouteKey.php`, `HttpRequestRouter.php`, `RouterConfig.php`

* 🧠 *Rešenje:*

  * `RouteKey` value object (`method`, `domain`, `pathTemplate`).
  * `DuplicateRouteException` u `registerRoute()`.
  * `RouterConfig->duplicatePolicy = THROW|REPLACE|IGNORE`.
  * Posebno pravilo za `ANY`: ne može pregaziti specifičan method.

* ✅ *Acceptance:* Garantovana jedinstvenost i stabilnost router state-a.

---

### ✅ **X3. Global state leakage eliminacija**

**Opis:** Refaktorisati `RouteCollector` da eliminiše global scope.

* 🔧 *Fajlovi:* `RouteCollector.php`, `RouterDsl.php`, `RouteRegistry.php`

* 🧠 *Rešenje:*

  * `RouteCollector` konstruiše se kao instanca u Router context-u.
  * Test: registracija u 2 paralelna konteksta (`api` i `web`) mora biti izolovana.
  * Ukloniti static buffer pristup.

* ✅ *Acceptance:* Router postaje potpuno thread-safe i izolovan po kontekstu.

---

### ✅ **X4. PHPDoc type alignment**

**Opis:** Uskladiti PHPDoc tipove sa realnom runtime strukturom podataka.

* 🔧 *Fajlovi:* `HttpRequestRouter.php`, `RouteDefinition.php`, `Router.php`

* 🧠 *Rešenje:*

  * Uvesti `@phpstan-type RoutesMap` za konzistentnu dokumentaciju.
  * Uvesti static rule koji proverava konzistentnost docblock tipova.
  * Korigovati `array<string, RouteDefinition[]>` vs `array<string, array<string, RouteDefinition>>`.

* ✅ *Acceptance:* IDE-friendly, 0 type mismatch situacija.

---

### ✅ **X5. Regex operations centralizacija**

**Opis:** Centralizovati regex operacije u `Support/functions.php`.

* 🔧 *Fajlovi:* `Support/functions.php`, `RouteDefinition.php`, `RouteMatcher.php`

* 🧠 *Rešenje:*

  * Helperi: `route_path()`, `route_constraint()`, `route_match()`, `route_compile()`.
  * Composer autoload "files" entry.
  * Sve PCRE greške mapirati u `InvalidConstraintException`.

* ✅ *Acceptance:* Regex kapsuliran, manji rizik bugova i brži matching.

---

### ✅ **X6. Cache manifest trust boundary**

**Opis:** Uvesti SHA256 cache manifest i fallback loader.

* 🔧 *Fajlovi:* `RouteCacheLoader.php`, `RouteCacheManifest.php`, `RouteBootstrapper.php`

* 🧠 *Rešenje:*

  * Hash manifest (`manifest.sha256`) i verifikacija pre učitavanja.
  * Ako hash ne odgovara, fallback na disk + log upozorenje.
  * Integrisati u bootstrap proces.

* ✅ *Acceptance:* Cache poisoning eliminisan, determinističko ponašanje.

---

### ✅ **X7. Evidence-based review system**

**Opis:** Dodati `Review-Evidence.md` tabelu sa proof-based review.

* 🔧 *Fajlovi:* `docs/Router/Review-Evidence.md`, `tests/ArchitectureTest.php`

* 🧠 *Rešenje:*

  * Tabela: Finding | File | Line | Evidence | Test | Status
  * Automatizovati update kroz CI (parse review + run codegrep).
  * Primer: RouteGroupStack static | Router/GroupStack.php | L22 | DI instance | test_GroupIsolation | ✅ Resolved

* ✅ *Acceptance:* Audit revizije postaje dokaziv i transparentan.

---

### ✅ **X8. Router developer helpers**

**Opis:** Uvesti domain-specific helper API za poboljšanu DX.

* 🔧 *Fajlovi:* `Support/functions.php`, `RouterDsl.php`, `RouterInterface.php`

* 🧠 *Rešenje:*

  * Domain-specific helperi: `route_group`, `route_any`, `route_constraint`.
  * Statički DSL builderi sa kontekst-aware fluent API-jem.
  * Namespaced funkcije za lakši onboarding.

* ✅ *Acceptance:* Veća čitljivost, brži onboarding, manja kognitivna kompleksnost.

---

# ✅ **SUMMARY v2.1 (COMPLETED)**

| Level       | Scope                          | Tasks | Status          |
| ----------- | ------------------------------ | ----- | --------------- |
| 🟥 Critical | Architecture                   | 6     | ✅✅✅✅✅✅     |
| 🟧 High     | Runtime & Behavior             | 5     | ✅✅✅✅✅      |
| 🟨 Medium   | Performance & DX               | 4     | ✅✅✅✅        |
| 🟩 Low      | Docs & Standards               | 2     | ✅✅            |
| ⚙️ Extra    | Tooling                        | 2     | ✅✅            |
| 🏢 Enterprise| Architecture Consolidation     | 8     | ✅✅✅✅✅✅✅✅   |

---

**Total Progress v2.1: 29/29 tasks completed (100%)**

---

# 🚀 **HTTP FOUNDATION ROUTER v2.2 — ENTERPRISE TODO PLAN**

---

## 🟥 **PHASE 1 — CORE ARCHITECTURE REFINEMENT (CRITICAL)**

### ✅ **[R1] Eliminate Global Static State (RouteGroupStack Refactor)**

* **Problem:** `RouteGroupStack` koristi statički stack, što krši DDD i DI principe.
* **Goal:** Instance-based group context sa dependency injection.

* 🔧 *Fajlovi:* `RouteGroupStack.php`, `RouterDsl.php`, `RouteRegistrar.php`, `RouteBootstrapper.php`

* 🧠 *Rešenje:*

  * Napravi novu klasu `RouteGroupContext` sa metodama `push()`, `pop()`, `current()`.
  * Ubaci je kao dependency u `RouterDsl` i `RouteRegistrar`.
  * U `RouteBootstrapper` kreiraj instancu konteksta i prosledi kroz DI.
  * Dodaj `RouteGroupStackDeprecationTest` da potvrdi da statička upotreba više ne postoji.

* ✅ *Success Criteria:*

  * Nema statičkih svojstava u Router namespace-u
  * Test izolacija 100% uspešna
  * Kontekst injektovan iz RouterBootstrapper-a

* 🧱 *Impact:* Thread-safe, test-safe, async-ready design
* 🟥 *Priority:* Critical

---

### ✅ **[R2] Normalize Exception Taxonomy**

* **Problem:** Mešanje `RuntimeException`, `LogicException`, i domain-specifičnih klasa.
* **Goal:** Jedinstvena hijerarhija iz korena `RouterExceptionInterface`.

* 🔧 *Fajlovi:* `RouterExceptionInterface.php`, `RouterException.php`, `RouteNotFoundException.php`, `ReservedRouteNameException.php`, `MethodNotAllowedException.php`

* 🧠 *Rešenje:*

  * Napravi `RouterExceptionInterface`.
  * Napravi apstraktnu `RouterException` klasu koja ga implementira.
  * Sve izuzetke (`RouteNotFoundException`, `ReservedRouteNameException`, …) refaktoriši da nasledjuju tu bazu.
  * Dodaj `RouterExceptionConsistencyTest` da verifikuje da svi izuzetci implementiraju interfejs.

* ✅ *Success Criteria:*

  * 100% izuzetaka pokriveno testom
  * Jedinstveni namespace i uniformna struktura

* 🛡️ *Impact:* Debugging i tracing konzistentni
* 🟥 *Priority:* Critical

---

## 🟧 **PHASE 2 — PERFORMANCE & VALIDATION OPTIMIZATION (HIGH)**

### ☐ **[R3] Reflection Metadata Cache**

* **Problem:** `RouteDefinitionValidator` koristi reflection za svaku proveru.
* **Goal:** Cache reflektovanih metoda/atributa za višestruku upotrebu.

* 🔧 *Fajlovi:* `RouteDefinitionValidator.php`, `ReflectionCache.php`

* 🧠 *Rešenje:*

  * Uvedi `private static array $reflectionCache` unutar validatora.
  * Koristi `spl_object_id()` ili `className::methodName` kao ključ.
  * Cache resetuj na `RouterBootstrapState::reset()`.

* ✅ *Success Criteria:*

  * <5% CPU overhead u benchmark testovima
  * Nema funkcionalne promene

* ⚡ *Impact:* 10–15% brže route validacije
* 🟧 *Priority:* High

---

### ✅ **[R4] RouteDefinition Hash Optimization**

* **Problem:** `var_export` kod generisanja cache-a je spor kod velikih ruta.
* **Goal:** Hash-based pre-validation i skip ako se route nije promenila.

* 🔧 *Fajlovi:* `RouteCacheManifest.php`, `RouteCacheWriter.php`

* 🧠 *Rešenje:*

  * U `RouteCacheManifest` dodaj `getHash(RouteDefinition $r): string`.
  * Ako se hash poklapa sa prethodnim manifestom, preskoči serializaciju.

* ✅ *Success Criteria:*

  * Cache generacija vreme smanjeno ≥40%
  * Funkcionalnost identična

* ⚡ *Impact:* Brži bootstrap, niže I/O troškove
* 🟧 *Priority:* High

---

## 🟨 **PHASE 3 — OBSERVABILITY & RELIABILITY (MEDIUM)**

### ✅ **[R5] RouterMetrics Alert Integration**

* **Problem:** Trenutno metrike postoje, ali bez integrisanih praga upozorenja.
* **Goal:** Automatska integracija sa Prometheus alert pravilima.

* 🔧 *Fajlovi:* `RouterMetricsCollector.php`, `metrics.alerts.yaml`, `.github/workflows/prometheus.yml`

* 🧠 *Rešenje:*

  * U `RouterMetricsCollector` dodaj alert pragove (`route_resolution_failures`, `cache_invalidations`).
  * Dodaj konfigurabilni YAML fajl (`metrics.alerts.yaml`).

* ✅ *Success Criteria:*

  * Prometheus eksport prikazuje "alert" etikete
  * Test: simulacija greške → alert aktiviran

* 🧠 *Impact:* Proaktivno praćenje performansi
* 🟨 *Priority:* Medium

---

### ✅ **[R6] RouterTrace Context Enrichment**

* **Problem:** Tracing ima precizan timing, ali nedovoljno konteksta.
* **Goal:** Dodati `request_id`, `route_name`, `middleware_count`.

* 🔧 *Fajlovi:* `RouterTrace.php`, `RouterKernel.php`, `RouteResolutionContext.php`

* 🧠 *Rešenje:*

  * `RouterTrace::record(string $event, array $context = [])`
  * Automatsko uključivanje metapodataka iz `RouteResolutionContext`.
  * Uvesti JSON log format kompatibilan sa ELK.

* ✅ *Success Criteria:*

  * Trace log sadrži minimum tri konteksta po događaju
  * ELK pipeline prepoznaje event strukturu

* 🔍 *Impact:* Viša dijagnostička vrednost logova
* 🟨 *Priority:* Medium

---

## 🟩 **PHASE 4 — TOOLING & AUTOMATION (LOW)**

### ✅ **[R7] Static Analysis Quality Gate**

* **Problem:** Trenutno statička analiza nije deo CI procesa.
* **Goal:** Automatski PHPStan, Psalm i Rector workflow.

* 🔧 *Fajlovi:* `.github/workflows/quality-check.yml`, `composer.json`

* 🧠 *Rešenje:*

  * Kreiraj `.github/workflows/quality-check.yml`
  * Koristi `composer check` skriptu:

    ```json
    "scripts": {
      "check": "phpstan analyse --level=max && psalm --no-cache"
    }
    ```

* ✅ *Success Criteria:*

  * CI blokira merge ako statička analiza padne

* 🧩 *Impact:* Automatski kvalitetni prag
* 🟩 *Priority:* Low

---

### ✅ **[R8] Documentation Auto-Sync Script**

* **Problem:** Dokumentacija mora ručno da se ažurira.
* **Goal:** Automatizovano osvežavanje dijagrama i sistemskih prikaza.

* 🔧 *Fajlovi:* `scripts/sync-docs.php`, `docs/Router/Architecture.md`, `docs/Router/Failure-Modes.md`

* 🧠 *Rešenje:*

  * Napravi `scripts/sync-docs.php`
  * Parsira PHPDoc anotacije i automatski generiše:

    * `docs/Router/Architecture.md` mermaid dijagram
    * `docs/Router/Failure-Modes.md` listu izuzetaka

* ✅ *Success Criteria:*

  * Pokretanjem `composer docs:sync` generiše ažurne fajlove

* 🧾 *Impact:* Uvek aktuelna dokumentacija
* 🟩 *Priority:* Low

---

## ⚙️ **PHASE 5 — QUALITY & STRESS TESTING (EXTRA)**

### ✅ **[R9] Chaos & Stress Test Expansion**

* **Goal:** Validirati fault-tolerance u ekstremnim uslovima.

* 🔧 *Fajlovi:* `tests/RouterChaosTest.php`

* 🧠 *Rešenje:*

  * U `tests/RouterChaosTest.php` dodaj simulacije:

    * Cache corruption
    * Concurrent bootstrap
    * Middleware chain interruption
  * Uporedi latenciju i logiku fallback-a.

* ✅ *Success Criteria:*

  * 100% predvidivo ponašanje i oporavak

* 🧪 *Impact:* Validirano fault-tolerance
* ⚙️ *Priority:* Extra

---

### ✅ **[R10] Enterprise Benchmarks**

* **Goal:** Kvantifikovati performanse.

* 🔧 *Fajlovi:* `benchmarks/RouterBenchmark.php`

* 🧠 *Rešenje:*

  * `benchmarks/RouterBenchmark.php` sa:

    * 10.000 route pattern testova
    * domain match i fallback testove
  * Koristi `phpbench` framework.

* ✅ *Success Criteria:*

  * Performanse unutar SLA (<1ms per resolve)

* 📊 *Impact:* Dokumentovana performance karakteristika
* ⚙️ *Priority:* Extra

---

## 📅 **EXECUTION ORDER (Recommended)**

1. 🟥 R1 — RouteGroupStack Refactor
2. 🟥 R2 — Exception Taxonomy Cleanup
3. 🟧 R3 — Reflection Cache
4. 🟧 R4 — Cache Hash Optimization
5. 🟨 R5 — Metrics Alerts
6. 🟨 R6 — Trace Enrichment
7. 🟩 R7 — Static Analysis Gate
8. 🟩 R8 — Auto-Docs Sync
9. ⚙️ R9 — Chaos Testing
10. ⚙️ R10 — Benchmarks

---

## ✅ **SUCCESS DEFINITION (v2.2 Milestone)**

| Category            | Target                                     | Metric                |
| ------------------- | ------------------------------------------ | --------------------- |
| **Architecture**    | 100% isolation, no static globals          | Reflection test suite |
| **Performance**     | +15% faster bootstrap, -40% cache gen time | PhpBench metrics      |
| **Security**        | Unified exception taxonomy                 | OWASP compliance      |
| **Observability**   | Alerts & enriched logs                     | Prometheus + ELK      |
| **Maintainability** | CI Quality Gates                           | GitHub Actions        |
| **Documentation**   | Auto-synced architecture                   | Docs auto-generated   |

---

# 📊 **v2.2 PROGRESS SUMMARY**

| Phase       | Scope                          | Tasks | Status |
| ----------- | ------------------------------ | ----- | ------ |
| 🟥 Critical | Core Architecture              | 2     | ✅✅    |
| 🟧 High     | Performance & Validation       | 2     | ✅✅    |
| 🟨 Medium   | Observability & Reliability    | 2     | ✅✅    |
| 🟩 Low      | Tooling & Automation           | 2     | ✅✅    |
| ⚙️ Extra    | Quality & Stress Testing       | 2     | ✅✅    |

---

**Total Progress v2.2: 10/10 tasks completed (100%)**

---

> **Napomena za "malog":**
>
> * Odradi po prioritetu (🟥 → ⚙️).
> * Za svaku stavku popuni checkbox ✅ nakon merge-a u main branch.
> * Po završetku svakog "Phase-a", izvrši `vendor/bin/phpunit --testsuite=router`
>   i zabeleži `Execution Summary` u `review.md`.

---

> **Napomena za "malog":**
>
> * Odradi po prioritetu (🟥 → ⚙️ → 🏢).
> * Za svaku stavku popuni checkbox ✅ nakon merge-a u main branch.
> * Po završetku svakog "Level-a", izvrši `vendor/bin/phpunit --testsuite=router`
>   i zabeleži `Execution Summary` u `review.md`.

---
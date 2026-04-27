Ima viška. I ne samo viška, nego ima nekoliko mesta gde se vidi da repo trenutno ima **dve arhitekture plus migration lepak**.

Hladan review: **pravac je dobar, ali trenutno stanje nije čisto.** `framework/System` je dobar potez. `PublicSurface`, runtime lifecycle, worker flow, request scope i state reset su dobri. Ali oko toga još stoji gomila starog root-a, duplih ownership-a, generated fajlova, backup dumpova, namespace bridge-eva i polumigriranih komponenti.

Najbitnije: ovo sada nije rewrite problem. Ovo je **normalization problem**.

## Brzi sud

```text
Decision: Keep and Improve

Framework axis: good
Migration discipline: partial
Current repo cleanliness: weak
Main risk: duplicate ownership + namespace drift
Worst smell: old roots and new roots living together
Next action: cleanup and ownership normalization, not more feature expansion
```

U uploadovanom review-u već stoji da je `framework/System` uveden kao novi lifecycle owner, da se postojeći kod reusuje gde je bezbedno, i da su compatibility aliasi namerno uski. To je dobro. Ali isti review kaže da je najviši nerešen severity i dalje **High**, uz namespace drift, static-analysis probleme i non-migrated DataHandling/HTTP legacy blokere. 

---

## Šta mi se dopada

`framework/System` je dobar novi centar. Ima `PublicSurface`, `Flows`, `Capabilities`, `Configuration`, `Foundation`. To je prava osa za Avax framework runtime. Review pokazuje da su dodati runtime abstractions, request scope, state reset, HTTP flow, console flow, worker runtime loop, shutdown flow, runtime public surface i runtime adapter shells. To je suštinski pravac koji smo hteli. 

`HandleWorkerRequest`, `StateReset`, `RequestScope`, `RuntimeKernel` su naročito bitni. To je ono što Avax razlikuje od “još jednog PHP component repo-a”.

`components/compat.php` je za sada razuman kao uski bridge. Review kaže da šire aliasovanje može da pokvari autoload i da compatibility bridge treba ostati uzak dok se HTTP/router/request namespace-i ne normalizuju. To je ispravno. 

---

## Šta mi se ne dopada

Prvo, imaš **previše root-level ostataka**.

Ovo je problematično:

```text
System/
DI/
ServerRequest/
Components/
Auth/
Providers/
Traits/
Writers/
Integrations/
integrations/
Config/
Presentation/
bootstrap/
public/
scripts/
tools/
var/
```

Neki od ovih foldera verovatno pripadaju starim komponentama, neki su legacy app shell, neki su tooling, neki su generated/runtime output. U novoj arhitekturi oni ne smeju živeti kao ravnopravni root-ovi.

Repo root treba da bude dosadan:

```text
framework/
components/
docs/
tests/
examples/
tooling/
bin/
config? only if repo-level config is truly needed
```

Sve ostalo mora ili da se premesti, ili obriše, ili eksplicitno označi kao compatibility bridge.

Drugo, `System/` na root-u je ozbiljan smell. Iz uploadovanog tree-ja se vidi ogroman top-level `System/` koji izgleda kao Auth sistem, sa `Auth.php`, `AuthInterface.php`, `Capabilities/Access`, `ExternalIdentity`, `Diagnostics`, itd. To ne sme biti root-level system za ceo repo. Ako je to Auth komponenta, mora živeti u:

```text
components/Auth/System/
```

Ako je stari Auth snapshot, briše se. Ako je compatibility bridge, mora biti minimalan i označen.

Treće, imaš **Data duple vlasnike**.

Trenutno postoje:

```text
components/Data/
components/DataFoundation/
components/DataLayer/
components/Persistence/
```

To je trenutno najopasniji deo, jer smo već odlučili finalnu podelu:

```text
DataFoundation -> Data
DataLayer      -> Persistence
Database       -> Database
```

Ne smeš imati `Data` i `DataFoundation` kao dva realna owner-a. Ne smeš imati `Persistence` i `DataLayer` kao dva realna owner-a. To mora da bude staged migration, ali sa jednim jasnim finalnim owner-om.

Četvrto, `Database` i dalje ima ORM/persistence stvari. U Database review-u stoji da Database trenutno pokriva connections, query, migrations, transactions, ali i ORM metadata, hydration, identity map i repositories.  To je bilo OK u starom kontekstu, ali u novoj podeli moraš preseći:

```text
Database = SQL/database mechanics
Persistence = EntityManager, Repository, UnitOfWork, IdentityMap, Hydration
Data = in-memory data structures
```

Ne moraš to sve pomeriti odmah, ali mora postojati migration map.

Peto, `Config/` root i `components/Config/` su potencijalno pomešani koncepti.

`components/Config` je reusable config component.

Root `Config/` deluje kao aplikacioni config:

```text
Config/app.php
Config/bootstrap.php
Config/database.php
Config/filesystems.php
Config/middleware.php
```

To ne treba da stoji u framework repo root-u kao production architecture. To treba da ode u:

```text
examples/minimal-http-app/config/
```

ili u test fixtures, osim ako je zaista repo-level config za sam framework.

Šesto, `bootstrap/bootstrap.php` je mrtav ili opasan. Review eksplicitno kaže da stari bootstrap importuje `AppFactory` koji ne postoji i da ne može biti validan lifecycle owner. Novi owner je `framework/System/PublicSurface/Avax.php`. 

Sedmo, governance fajl ima typo:

```text
AI Prompts/how-to-arhitecture-extension.md
```

Treba:

```text
AI Prompts/how-to-architecture-extension.md
```

Ovo nije estetski problem. Governance discovery može da promaši dokument ili da kasnije dobiješ dva fajla sa skoro istim imenom.

Osmo, `.agents` unutar `components/Auth` je prevelik i verovatno ne pripada production component tree-u. Ako je to agent governance, prebaci u repo-level `.agents/`, `docs/governance/`, ili ga izbaci iz package/autoload/distribution. Komponenta ne sme nositi ceo agent operativni sistem u sebi.

Deveto, generated/cache/backup fajlovi ne smeju biti deo repozitorijuma:

```text
avax-backup.txt
.php-cs-fixer.cache
.phpunit.result.cache
.ruff_cache/
.gigaide/
var/
```

Ako je `avax-backup.txt` stvarno u repo-u, obriši ga odmah. To pravi lažan tree i duplira ceo sadržaj u analizama. Ako je samo artifact iz merge skripte, isključi ga iz sledećeg dump-a.

Deseto, `components/Facade` kao komponenta ne treba da postoji. `Facade` nije capability. Facade je public-surface pattern. Treba ga rasturiti po komponentama:

```text
components/Router/System/PublicSurface/Facades/Route.php
components/Cache/System/PublicSurface/Facades/CacheFacade.php
```

Ne:

```text
components/Facade/
```

---

## Prioriteti, hladno poređani

### P0: Zaustavi dalje širenje

Ne dodavati nove komponente, runtime adaptere, async, websocket, queue, scheduler, ORM feature-e, dok se ne očisti ownership.

Sada nije trenutak za još moći. Sada je trenutak za čistoću.

### P1: Ukloni lažne root-ove

Ovo je najbitnije.

Root mora ostati:

```text
framework/
components/
docs/
tests/
examples/
tooling/
bin/
```

Sve ostalo mora imati razlog.

### P2: Sredi namespace drift

Review i risk register eksplicitno kažu da mixed `Avax\...` i `components\...` namespace-i i dalje postoje i da PHPStan ne može pošteno da proveri bridge dok request/router subtree nije normalizovan. 

### P3: Sredi Data stack

`DataFoundation`, `DataLayer`, `Data`, `Persistence`, `Database` moraju dobiti jasan migration map.

### P4: Sredi HTTP bridge

Framework HTTP je sada executable, ali još zavisi od legacy request/router bridge-a. Sledeći realan korak je namespace-normalizovan Request/Router/Middleware path.

### P5: Sredi tooling

Risk register kaže da `composer validate` i dev-tool constraints još imaju drift, a review kaže da Rector toolchain ne radi pouzdano pod PHP 8.5. 

---

# AI ToDo list

Ovo možeš direktno dati Codex-u.

```text
You are working on Avax, a runtime-agnostic PHP framework migration.

Goal:
Clean and normalize the current refactor without adding new feature scope.

Primary rule:
Do not expand the framework. Reduce duplicate ownership, remove stale roots, normalize namespaces, and protect the new framework/System axis.

Current target architecture:
framework/System/
  PublicSurface/
  Flows/
  Capabilities/
  Configuration/
  Foundation/

components/<Component>/System/
  PublicSurface/
  Flows/
  Capabilities/
  Configuration/
  Foundation/

docs/
tests/
examples/
tooling/
bin/

Strict rules:
- Follow every how-to-*.md governance document.
- Treat framework/System as the canonical framework lifecycle owner.
- Do not create new feature folders.
- Do not widen components/compat.php.
- Do not introduce more runtime aliases.
- Do not duplicate component owners.
- Do not keep dead root-level folders.
- Do not move behavior without characterization tests.
- Do not rewrite existing working components.
- Normalize ownership before expanding behavior.

Phase 1: Repo root cleanup inventory

Create:
Code-Review-And-ToDo/root-cleanup-inventory.md

Inventory every top-level folder/file and classify it as one of:
- keep
- move to framework/
- move to components/
- move to docs/
- move to tests/
- move to examples/
- move to tooling/
- delete
- temporary compatibility bridge

Must inspect and classify at least:
- System/
- DI/
- ServerRequest/
- Components/
- Auth/
- Providers/
- Traits/
- Writers/
- Integrations/
- integrations/
- Config/
- Presentation/
- bootstrap/
- public/
- scripts/
- tools/
- var/
- .gigaide/
- .ruff_cache/
- .php-cs-fixer.cache
- .phpunit.result.cache
- avax-backup.txt
- components/Facade
- components/tests
- components/Avax.php
- components/compat.php

Do not delete yet. First produce the inventory and proposed action.

Phase 2: Governance filename correction

Fix the misspelled architecture extension filename.

Required action:
- Rename AI Prompts/how-to-arhitecture-extension.md to AI Prompts/how-to-architecture-extension.md
- Update every reference to the misspelled filename
- Update Code-Review-And-ToDo/review.md governance inventory
- Add a note that this was a governance discovery bug
- Run grep to ensure "arhitecture" no longer exists except in old logs if intentionally preserved

Phase 3: Delete generated/cache/backup artifacts

Remove from repository if present:
- avax-backup.txt
- .php-cs-fixer.cache
- .phpunit.result.cache
- .ruff_cache/
- var/ if it contains runtime-generated state only
- any generated merged repository dump files

Update .gitignore to include:
- *.cache
- .phpunit.result.cache
- .php-cs-fixer.cache
- .ruff_cache/
- var/
- *backup*.txt
- merged-output files if used by local tooling

Do not delete source files named avax or bin/avax.

Phase 4: Remove or relocate invalid root-level architecture

Handle root-level folders:

1. System/
   - If it is Auth, move or map it to components/Auth/System/
   - If it duplicates components/Auth/System, delete the duplicate
   - If it is compatibility-only, reduce it to a minimal bridge and document removal
   - It must not remain as root-level System/

2. DI/
   - Move real DI behavior into components/Container/System/
   - Delete duplicate DI wrappers
   - Keep only compatibility bridge if needed

3. ServerRequest/
   - Move into components/HTTP or components/Request depending current ownership
   - Do not keep root ServerRequest/

4. Auth/
   - Move into components/Auth/ or delete if duplicate
   - Do not keep root Auth/

5. Providers/
   - Move provider behavior into each component's System/Configuration/
   - Do not keep root Providers/

6. Traits/
   - Move traits to the true owning component
   - Delete generic trait bucket if possible

7. Writers/
   - If logging/output related, move to components/Logging/System/Capabilities/Writers/
   - If filesystem related, move to components/Filesystem/
   - Do not keep root Writers/

8. Integrations/ and integrations/
   - Normalize casing
   - If runtime adapters, move to framework/System/Capabilities/Runtime/Adapters/
   - If external service integrations, create components/Integration only if real capability exists
   - Otherwise delete or move to examples/tooling

9. Config/
   - Move app example config to examples/minimal-http-app/config/
   - Move framework config fixtures to tests/Support/Fixtures/config/
   - Keep root Config/ only if it is explicitly repo runtime config and documented

10. Presentation/
    - Move routes to examples/minimal-http-app/app/routes.php or tests fixtures
    - Framework should not depend on root Presentation/ as a long-term source

11. bootstrap/
    - Replace bootstrap/bootstrap.php with a compatibility wrapper to framework/System/PublicSurface/Avax.php or delete it
    - Do not let bootstrap/ own lifecycle

Phase 5: Data stack normalization plan

Create:
Code-Review-And-ToDo/data-stack-normalization.md

Final ownership:
- components/DataFoundation -> components/Data
- components/DataLayer -> components/Persistence
- components/Database remains components/Database

Rules:
- Data owns in-memory data structures and transformations
- Database owns connections, query, transactions, schema, migrations
- Persistence owns EntityManager, Repository, UnitOfWork, IdentityMap, Mapping, Hydration, ChangeTracking
- Database must not own ORM long-term
- Data must not depend on Database or Persistence
- Database must not depend on Persistence
- Persistence may depend on Database contracts and Data

Specific tasks:
- Inventory DataFoundation
- Inventory DataLayer
- Inventory Database ORM-related files
- Move or map DataFoundation files into Data
- Move or map DataLayer files into Persistence
- Extract Database ORM capabilities into Persistence migration plan
- Keep compatibility bridges only temporarily
- Add removal plan for DataFoundation and DataLayer old namespaces

Phase 6: HTTP/Request/Router namespace normalization

Goal:
Make framework/System/Flows/HandleIncomingHttp statically analyzable without widening components/compat.php.

Tasks:
- Inventory all Request, Response, Router, Middleware classes used by framework HTTP bridge
- Choose canonical namespace for each subtree
- Normalize reused classes to Avax\...
- Remove components\... declarations from migrated slices
- Update imports in framework/System/Flows/HandleIncomingHttp
- Keep components/compat.php narrow
- Add tests proving route-backed HTTP still works
- Run PHPStan against framework/System/Flows/HandleIncomingHttp

Do not migrate unrelated HTTP client code in this phase.

Phase 7: Middleware integration

Goal:
Stop framework HTTP from bypassing middleware.

Tasks:
- Identify current Middleware component owner
- Rename components/Middlewares to components/Middleware if both exist
- Move/normalize middleware pipeline into components/Middleware/System/
- Add framework bridge from HandleIncomingHttp to Middleware pipeline
- Add integration test:
  test_it_runs_middleware_before_route_action()
- Add integration test:
  test_it_short_circuits_when_middleware_returns_response()

Phase 8: Console integration

Goal:
Move framework console from metadata reuse to real command execution.

Tasks:
- Inventory components/Commands
- Decide final owner:
  components/Console/System/
- Move command catalog and command execution there
- Keep Commands as compatibility bridge only if needed
- Wire framework/System/Flows/RunConsoleCommand to Console component
- Add feature test for bin/avax running a framework command
- Remove stale command aliases

Phase 9: Database/Persistence split preparation

Do not move ORM code yet unless tests exist.

Tasks:
- Mark Database ORM folders as "to be extracted to Persistence"
- Add characterization tests for current EntityManager, Repository, UnitOfWork behavior
- Create Persistence migration map
- Create compatibility policy
- Do not create duplicate EntityManager public APIs
- Decide whether public EntityManager lives in Persistence/PublicSurface or Database compatibility facade

Phase 10: Docs mirror cleanup

Tasks:
- Move docs/Foundation/DataLayer to docs/components/Persistence
- Move docs/Foundation/DataHandling to docs/components/Data
- Move docs/Foundation/Database to docs/components/Database if still present
- Delete or redirect obsolete docs/Foundation tree
- Ensure docs/framework/System mirrors framework/System
- Ensure docs/components/<Component>/System mirrors migrated components
- Add how-this-works.md only for real ownership folders
- Do not keep docs for deleted structures

Phase 11: Tests cleanup

Tasks:
- Move tests/Foundation/DataHandling to tests/Unit/Components/Data or tests/Integration/Components/Data
- Move tests/Foundation/Container to tests/Unit/Components/Container
- Move legacy HTTP/router tests to tests/Unit/Components/HTTP or tests/Unit/Components/Router
- Remove tests for deleted dead architecture
- Add characterization tests before moving behavior
- Keep behavior names, not implementation names

Phase 12: Quality gates

Run in this order:
1. composer validate --no-check-publish
2. composer dump-autoload -o
3. php -l on changed PHP files
4. targeted PHPUnit for framework/System
5. targeted PHPUnit for affected component
6. PHPStan on framework/System and affected component
7. docs validation
8. runtime leak checker
9. duplicate owner checker
10. kluster code verification if configured

Expected final report:
Create Code-Review-And-ToDo/cleanup-review.md with:
- GOVERNANCE INVENTORY
- ROOT CLEANUP MATRIX
- DUPLICATE OWNER REPORT
- NAMESPACE NORMALIZATION REPORT
- DATA STACK MIGRATION REPORT
- REMOVED FILES/FOLDERS
- COMPATIBILITY BRIDGES KEPT
- COMPATIBILITY BRIDGES REMOVED
- TEST RESULTS
- STATIC ANALYSIS RESULTS
- REMAINING RISKS
- NEXT STEPS
```

---

## Moj konkretan cleanup matrix

Ovo bih odmah sproveo kao odluku, posle inventory-ja:

```text
DELETE:
  avax-backup.txt
  .php-cs-fixer.cache
  .phpunit.result.cache
  .ruff_cache/
  components/Facade/
  components/tests/
  components/new-component.md
  stale component-local ToDo/review docs if duplicated under Code-Review-And-ToDo

MOVE:
  Config/* -> examples/minimal-http-app/config/ or tests/Support/Fixtures/config/
  Presentation/HTTP/routes/* -> examples/minimal-http-app/app/routes.php or tests fixtures
  scripts/* -> tooling/
  tools/* -> tooling/
  public/* -> examples/minimal-http-app/public/ unless framework package needs it
  docs/Foundation/DataLayer -> docs/components/Persistence
  docs/Foundation/DataHandling -> docs/components/Data
  tests/Foundation/* -> tests/Unit/Components/* or tests/Integration/Components/*

NORMALIZE:
  DataFoundation -> Data
  DataLayer -> Persistence
  Commands -> Console
  Middlewares -> Middleware
  ApplicationWorkflow -> Workflow
  DumpDebugger -> Diagnostics or tooling/debug
  Security -> Auth/Security or components/Security, but only if real standalone capability

KEEP:
  framework/System
  components/Auth
  components/Cache
  components/Container
  components/Database
  components/HTTP for now, until split decision
  components/Config
  components/Events if present
  components/Filesystem
  components/Logging
  components/Validation
  components/View
  components/Session, after normalization

TEMPORARY ONLY:
  components/compat.php
  old DataFoundation namespace bridge
  old DataLayer namespace bridge
  old bootstrap wrapper if external scripts still depend on it
```

---

## Najveća greška koju sada treba izbeći

Nemoj dozvoliti AI agentu da “popravlja” ovo tako što će dodati još bridge-eva, još aliasa i još foldera.

Tvoj sledeći refactor treba da bude **reduction pass**:

```text
less roots
less aliases
less duplicate docs
less stale tests
less namespace ambiguity
less fake ownership
```

Tek posle toga ide dalje širenje framework-a.

Hladno: framework core izgleda obećavajuće. Repo oko njega još nije čist. Sada praviš hiruršku fazu čišćenja. 🧊

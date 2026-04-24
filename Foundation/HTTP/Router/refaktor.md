# Status: završeno

Ovaj refactor plan je implementiran kroz novu `System` strukturu:

* `System/Flows/RegisterRoutes`
* `System/Flows/BootstrapRoutes`
* `System/Flows/ResolveRequest`
* `System/Flows/RunRoute`
* `System/Capabilities`
* `System/Configuration`
* `System/Foundation`

Stara paralelna Router struktura (`Routing`, `Support`, `Bootstrap`, `Cache`, `Kernel`, `Validation`, `Tracing`,
`Matching`, `Metrics`, `Snapshots`, `Exceptions`) je uklonjena nakon Composer autoload smoke provere.
Detaljan review i završni plan su u `Code-Review-And-ToDo/review.md` i `Code-Review-And-ToDo/refactor-plan.md`.

Idemo na precizan plan, bez magle. Osnova plana je da zadrzis javni ugovor stabilnim oko `RouterInterface` i `Router`,
da ne radis big-bang rewrite, i da novu strukturu preseces po 4 stvarna toka koja vec postoje u komponenti: DSL
registracija, bootstrap, runtime resolution i execution pipeline. To je u skladu i sa samim Router principima i sa
how-to pravilima o flow-first citanju, maloj javnoj povrsini, incremental refactoru i TDD-u.

Predlazem da ovo bude finalni refactor plan za Router komponentu.

## 1. Cilj refaktora

Cilj nije samo lepsi tree. Cilj je da Router pocne da se cita kao istinita prica sistema:

1. kako se ruta registruje
2. kako se bootstrapuje
3. kako se request razresava
4. kako se ruta izvrsava

To direktno prati tvoj trenutni request lifecycle i arhitekturu komponentе.

## 2. Ne dirati ove stvari

Ovo su non-negotiables:

* `RouterInterface` i `Router` ostaju BC surface
* spoljne PSR integracije i runtime contract ostaju stabilni
* ponasanje ostaje isto dok testovi ne potvrde promenu
* refaktor ide u malim bezbednim koracima
* mrtve stare strukture se na kraju brisu, ne ostavljaju se zauvek kao sinonimi

## 3. Finalni target tree

Ne ponavljam ga 100% do poslednjeg fajla, nego ga fiksiram kao operativni cilj:

```text
Router/
  Router.php
  RouterInterface.php
  RouterRuntimeInterface.php
  HttpMethod.php
  functions.php

  System/
    Flows/
      RegisterRoutes/
      BootstrapRoutes/
      ResolveRequest/
      RunRoute/

    Capabilities/
      RouteDefinition/
      RouterTrace/
      RouterMetrics/

    Configuration/
      RouterConfig.php

    Foundation/
      Exceptions/

  docs/
    Router/
      how-this-works.md
      System/
        Flows/
          RegisterRoutes/how-this-works.md
          BootstrapRoutes/how-this-works.md
          ResolveRequest/how-this-works.md
          RunRoute/how-this-works.md

  tests/
    Architecture/
    Flows/
    Integration/
    Chaos/

  Code-Review-And-ToDo/
    review.md
```

Ovo prati how-to pravilo da system root treba da sece na Flows, Capabilities, Configuration i Foundation, a da se
arhitektura cita kao flow -> slice -> unit -> function.

## 4. Redosled rada

Nemoj raditi sve odjednom. Radi ovim redom.

### Faza 0. Freeze ponašanja

Prvo zakucaj characterization testove za postojece ponasanje.

ToDo:

* pokrij registraciju osnovnih ruta
* pokrij group prefix / middleware / constraints nasledivanje
* pokrij fallback rutu
* pokrij bootstrap iz diska
* pokrij bootstrap iz cache-a
* pokrij 404 vs 405
* pokrij param extraction i constraint validation
* pokrij middleware pipeline redosled
* pokrij dispatch do controller-a / action-a
* pokrij route cache manifest mismatch
* pokrij closure route behavior
* pokrij snapshot export kao best-effort, da ne rusi bootstrap ako failuje

Ovo direktno prati TDD i legacy rescue pravila. Refaktor bez ovoga je kockanje.

### Faza 1. Uvedi novo stablo bez promene logike

Samo napravi novu strukturu foldera i premesti dokumentaciju i test root-ove. Ne menjaj logiku osim namespace / import
prilagodjavanja.

ToDo:

* napravi `System/Flows`
* napravi `System/Capabilities`
* napravi `System/Configuration`
* napravi `System/Foundation/Exceptions`
* napravi `docs/Router/...`
* napravi `tests/Flows/...`
* napravi `Code-Review-And-ToDo/review.md`

Dokumentacija mora da ide pod top-level `docs/`, da mirroruje source tree, i svaki ownership folder mora imati
`how-this-works.md`.

### Faza 2. RegisterRoutes flow

Ovo je prvi pravi rez, jer tu Router dobija shape.

Smisao:

* DSL registracija vise ne sme da zivi razbacana po `Routing`, `Support` i helperima
* group state i registry state moraju da budu lokalni, jasni, i bez laznih generic bucket-a

ToDo:

* premesti `RouterDsl` u `System/Flows/RegisterRoutes/RouterDsl.php`
* premesti `RouteBuilder` u `System/Flows/RegisterRoutes/Definitions/RouteBuilder.php`
* premesti `RouteRegistry` u `System/Flows/RegisterRoutes/Definitions/RouteRegistry.php`
* premesti `RouteRegistrarProxy` u `System/Flows/RegisterRoutes/Files/RouteRegistrarProxy.php`
* premesti `RouteGroupContext` u `System/Flows/RegisterRoutes/Groups/RouteGroupContext.php`
* `RouteGroupStack` preimenuj i preseli u nesto istinitije, tipa `RouteGroupFrames.php` ili `RouteGroupState.php`, pod
  `Groups/`
* uvedi jasan owner fajl `RegisterRoutes.php` kao root owner flow-a
* izdvoji `BuildRouteDefinition.php` ako build logika u `RouteBuilder` postane preteska
* fallback registraciju odvoji u `RegisterFallbackRoute.php`
* attribute route registraciju drzi u `Attributes/`
* ukloni `Support/RouteCollector` kao genericki bucket ako mu je jedina realna uloga registracija ruta, ili ga prebaci
  pod `Files/RouteCollector.php`

Poseban rez:

* sve genericke reci tipa `Support`, `Helper`, `Manager`, `Processor` treba ukloniti gde ne predstavljaju stvarnu
  granicu
* `functions.php` zadrzi kao javni DSL helper sloj samo ako je to zaista deo public ergonomics surface, a ne rupa kroz
  koju curi interni haos

### Faza 3. BootstrapRoutes flow

Ovo je drugi rez. Trenutni `RouteBootstrapper` vec pokazuje da je bootstrap poseban flow sa odlukom cache vs disk, disk
discovery, isolated require, registry flush, fallback registracijom, cache write i snapshot export-om. To je dobar
kandidat za jedan jasan flow owner.

ToDo:

* `Bootstrap/RouteBootstrapper.php` premesti u `System/Flows/BootstrapRoutes/BootstrapRoutes.php`
* razbij `RouteBootstrapper` na manje owner-e:

    * `Source/DecideBootstrapSource.php`
    * `Disk/DiscoverRouteFiles.php`
    * `Disk/LoadRoutesFromDisk.php`
    * `Cache/LoadRoutesFromCache.php`
    * `Cache/WriteRouteCache.php`
    * `Snapshot/ExportRouterSnapshot.php`
* `RouteCacheLoader`, `AsyncRouteCacheLoader`, `RouteCacheManifest` premesti pod `BootstrapRoutes/Cache/`
* `RouterBootstrapState` premesti pod `BootstrapRoutes/State/`
* `RouterSnapshot` premesti pod `BootstrapRoutes/Snapshot/` ili ostavi kao capability samo ako ga zaista koristi i nesto
  drugo osim bootstrap-a
* ukloni anonymous adapter unutar `exportRouterSnapshot()`
* umesto inline anonymous class uvedi named adapter, npr. `Snapshot/RouterRuntimeView.php`

Najbitnije ciscenje u ovoj fazi:

* `registerFallbackRoute()` trenutno izgleda sumnjivo jer radi nad `RouteRegistry` kao da je fallback handler, sto
  semanticki smrdi
* `loadClosureRoutesFromDisk()` deluje kao duplikat / dead-ish putanja u odnosu na glavni bootstrap flow
* komentarni sloj je bucan i cesto restates code umesto da objasni ownership i odluke

Ovo nije kozmetika. Ovo su realni kandidati za bug ili laznu arhitekturu.

### Faza 4. ResolveRequest flow

Ovo je srce runtime resolution-a. U tvojoj dokumentaciji je vec odvojeno kao poseban layer: `HttpRequestRouter`,
`RouteMatcher`, `RouteConstraintValidator`, 404/405 odluke, param extraction. Znači treba samo da dobije istinito
vlasnistvo.

ToDo:

* `HttpRequestRouter` premesti u `System/Flows/ResolveRequest/HttpRequestRouter.php`
* `RouteMatcher` premesti u `Matching/RouteMatcher.php`
* `DomainAwareMatcher` premesti u `Matching/DomainAwareMatcher.php`
* `RouteConstraintValidator` premesti u `Constraints/RouteConstraintValidator.php`
* `PathNormalizer` ili slicne path util-e premesti u `Paths/`
* uvedi `ResolveRequest.php` kao root owner runtime resolution flow-a
* uvedi `ResolveFallbackRoute.php` ako fallback resolution ima posebnu odluku
* uvedi `ApplyHeadRequestFallback.php` samo ako HEAD/GET specijalno ponasanje zaista postoji
* jasno odvoji:

    * normalizaciju request-a
    * match odluku
    * constraint validaciju
    * resolved route injection u request attributes
    * 404/405 razliku

Ova faza mora da rezultuje time da neko moze da otvori jedan flow i odmah vidi gde se donosi odluka "nije nadjena ruta"
a gde "method not allowed". To je bukvalno deo tvog error contract-a.

### Faza 5. RunRoute flow

Ovo je pipeline i dispatch deo.

ToDo:

* `RouterKernel` premesti u `System/Flows/RunRoute/RouterKernel.php`
* `RoutePipeline` premesti u `Pipeline/RoutePipeline.php`
* `RoutePipelineFactory` i `StageChain` premesti u `Pipeline/`
* `RouteExecutor` premesti u `Dispatch/RouteExecutor.php`
* `RouteMiddleware` ili middleware resolution logiku premesti u `Dispatch/`
* uvedi `RunRoute.php` kao root owner
* ako pipeline ordering pravila postoje, uvedi `RouteStage.php` / `StageOrderException.php`
* izoluj DI/container lookup iz hot path-a koliko mozes bez promene ponasanja
* jasno dokumentuj gde se request obogacuje resolved route informacijom pre dispatch-a

Ovo prati tvoju trenutnu execution layer podelu: `RouterKernel`, `RoutePipeline`, `RouteExecutor`.

### Faza 6. Capabilities lane

Ovo nisu flows. Ovo su stabilne stvari koje flows koriste.

ToDo:

* `RouteDefinition` izdvoji u `System/Capabilities/RouteDefinition/RouteDefinition.php`
* ako postoji `RouteKey`, `DuplicatePolicy`, `RouteCollection`, smesti ih tu ili pod `ResolveRequest/Matching` u
  zavisnosti od stvarnog ownership-a
* `RouterTrace` stavi u `System/Capabilities/RouterTrace/RouterTrace.php`
* `RouterMetricsCollector` uvedi samo ako vec postoji realna potreba, ne spekulativno
* sve sto je cista data shape ili capability contract drzi van flow owner-a

Ovde vazi pravilo da ne izmisljas capability samo da popunis lane. Capability mora da ima stvarno, stabilno vlasnistvo.

### Faza 7. Foundation i exceptions

ToDo:

* sve Router exceptions preseli u `System/Foundation/Exceptions/`
* standardizuj error taxonomy:

    * not found
    * method not allowed
    * duplicate route
    * invalid route
    * invalid constraint
    * reserved route name
    * unresolvable middleware
    * stage ordering
* dodaj dosledne `@throws` gde zaista mogu da se dese
* poruke gresaka ucini operativnim, da kazu sta je puklo i sta je sledece bitno

How-to clean code izricito trazi eksplicitne failure-e i male stabilne contracts.

### Faza 8. Documentation pass

Ovo nije opciono. Dokumentacija je design artifact.

ToDo:

* napravi `docs/Router/how-this-works.md`
* napravi `docs/Router/System/Flows/RegisterRoutes/how-this-works.md`
* napravi `docs/Router/System/Flows/BootstrapRoutes/how-this-works.md`
* napravi `docs/Router/System/Flows/ResolveRequest/how-this-works.md`
* napravi `docs/Router/System/Flows/RunRoute/how-this-works.md`
* svaki page mora imati frontmatter
* svaki page mora imati real trigger, real file/function names, real mermaid `sequenceDiagram`, sta se upisuje na disk,
  sta korisnik vidi, i gde debug prvo
* docs moraju mirrorovati source tree

To je bukvalno hard rule.

### Faza 9. Hygiene pass

Ovo radi tek kad ownership legne.

ToDo:

* obrisi redundantne komentare koji samo prepricavaju kod
* obrisi lazni ili raspali PHPDoc
* sredi import-e
* dodaj missing `@throws`
* izbaci generic nazive gde ne predstavljaju stvarnu granicu
* zadrzi samo maintenance komentare koji objasnjavaju zasto, ne sta
* proveri da li `functions.php` ostaje public ergonomic layer ili ide pod interni flow

To je trazeno coding standardima i clean code pravilima.

### Faza 10. Deletion phase

Tek na kraju.

ToDo:

* obrisi stare foldere koji su ostali kao lazni sinonimi
* obrisi obsolete wrappers
* obrisi duple ownere
* obrisi dead paths
* obrisi compatibility layer kada testovi i migracija potvrde da vise ne treba

Pravilo je jasno. Kad uvedes bolju strukturu, staru lazima ne ostavljas da zivi.

## 5. Konkretan ToDo po prioritetu

Evo ga kao radna lista za AI ili za tebe.

### P0

* [ ] napisati characterization test suite za kompletan trenutni Router lifecycle
* [ ] fiksirati public API contract test za `RouterInterface` i `Router`
* [ ] napraviti target tree pod `System/`
* [ ] napraviti `docs/Router/` mirror tree
* [ ] napraviti `tests/Flows/` tree

### P1

* [ ] preseliti DSL registraciju u `Flows/RegisterRoutes`
* [ ] izbaciti `Support/` kao primarni bucket
* [ ] lokalizovati group state ownership
* [ ] lokalizovati registry ownership
* [ ] izdvojiti fallback registration ownership

### P2

* [ ] preseliti bootstrap u `Flows/BootstrapRoutes`
* [ ] razbiti source decision, disk loading, cache loading, cache writing, snapshot export
* [ ] ukloniti anonymous class iz snapshot eksportа
* [ ] proveriti da li `loadClosureRoutesFromDisk()` treba da postoji
* [ ] ispraviti fallback semantics u bootstrap-u

### P3

* [ ] preseliti runtime matching u `Flows/ResolveRequest`
* [ ] jasno odvojiti 404 i 405 putanje
* [ ] jasno odvojiti path normalization, match, constraints, parameter injection
* [ ] smestiti domain matching gde realno pripada

### P4

* [ ] preseliti pipeline execution u `Flows/RunRoute`
* [ ] smanjiti magiju oko stage chain-a i middleware resolution-a
* [ ] dokumentovati tacan dispatch path
* [ ] proveriti hot path object churn i container lookup cost, ali tek nakon merenja

### P5

* [ ] izvući stabilne capability jedinice
* [ ] standardizovati exceptions
* [ ] hygiene pass nad komentarima i PHPDoc-om
* [ ] dodati how-this-works dokumentaciju za svaki ownership folder
* [ ] obrisati staru strukturu

## 6. Definition of Done

Refaktor je gotov tek kada vazi sve ovo:

* novi tree se cita kao 4 realna flow-a
* public API nije pukao
* svi characterization i integration testovi prolaze
* svi ownership folderi imaju `how-this-works.md`
* dokumentacija mirroruje source tree
* nema generic bucket-a koji lazu o ownership-u
* nema mrtvih sinonima stare arhitekture
* review moze jasno da kaze da je sistem laksi za citanje, menjanje i odrzavanje nego pre

## 7. Brutalno iskrena napomena

Najveci rizik nije tehnicki. Najveci rizik je da uradis polu-refaktor. To je najgora varijanta. Da dobijes malo lepse
foldere, a da ownership ostane mutan. Nemoj to. Ili preseci po flow-ovima do kraja, ili ne diraj jos. Tvoja pravila su
potpuno jasna po tom pitanju. Struktura mora da govori istinu.

Sledece sto ima smisla je da ti iz ovoga pretvorim sve u jedan jak AI prompt za Codex, sa eksplicitnim pravilima i
redosledom izvrsavanja.

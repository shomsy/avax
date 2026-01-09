# ⚙️ **KERNEL ORCHESTRATOR REFACTOR TODO**

> *Cilj: Pretvoriti ContainerKernel iz "mini containera" u čist, modularan i održiv **Kernel Orchestrator**.*

---

## 🧩 FAZA 1 — "Detektuj pregrejane zone"

**Cilj:** identifikovati sav kod koji ne pripada orchestration sloju.

📋 **Taskovi:**

- [ ] Otvori `ContainerKernel.php`
- [ ] Obeleži sve sledeće:
  - [ ] `if` i `switch` blokove
  - [ ] `try/catch` blokove
  - [ ] direktne reference na servise (`ResolutionEngine`, `ScopeManager`, `PolicyFlow`, `Metrics`)
  - [ ] metode koje imaju `boot`, `terminate`, `inject`, `store`, `invoke`
  - [ ] svaku promenljivu `$this->map`, `$this->cache`, `$this->instances`
- [ ] U komentar iznad svakog napiši:
  ```php
  // TODO move to step: <ime>
  ```
- [ ] Sačuvaj listu u `docs/kernel-hotspots.txt`.

✅ **Rezultat:** jasan spisak mesta gde Kernel radi previše.

---

## ⚙️ FAZA 2 — "Pomeranje odgovornosti u Steps"

**Cilj:** svaku granu logike iz kernela izdvojiti u novi `Step`.

📁 `src/Container/Core/Kernel/Steps/`

📋 **Taskovi:**

- [ ] Za svaku oznaku iz prethodne faze napravi novi `Step`:

  | Nova klasa                   | Poreklo logike                       |
  | ---------------------------- | ------------------------------------ |
  | `EnsureDefinitionExistsStep` | `if (!$this->definitions->has($id))` |
  | `ResolveInstanceStep`        | `ResolutionEngine->resolve()`        |
  | `InjectDependenciesStep`     | `injector->inject()`                 |
  | `StoreLifecycleStep`         | `ScopeManager->store()`              |
  | `CollectDiagnosticsStep`     | `Metrics->track()`                   |
  | `GuardPolicyStep`            | `PolicyFlow` provere                 |
  | `ErrorHandlingStep`          | `try/catch` blokovi                  |
  | `WarmUpStep` (opciono)       | `boot()` logika                      |

- [ ] Svi step-ovi implementiraju:
  ```php
  public function __invoke(KernelContext $ctx): void;
  ```

- [ ] Step-ovi koriste samo servis koji im se injektuje kroz konstruktor.
  ❌ Nema `$kernel` reference.
  ✅ Sve kompozicije idu kroz `ResolutionPipelineBuilder`.

✅ **Rezultat:** Kernel više ne zna *šta* se radi — pipeline zna.

---

## 🧱 FAZA 3 — "Čisti orkestrator"

**Cilj:** svesti `ContainerKernel` na minimalnu formu (redni tok i context).

📋 **Taskovi:**

- [ ] U `ContainerKernel.php` ostavi samo:
  ```php
  private readonly ResolutionPipeline $pipeline;
  private readonly DefinitionRepository $definitions;
  ```

- [ ] Obriši sve ostale property-je.

- [ ] Refaktori `resolve()`:
  ```php
  public function resolve(string $id): object
  {
      $ctx = new KernelContext($id, $this->definitions->get($id));
      $this->pipeline->run($ctx);
      return $ctx->instance;
  }
  ```

- [ ] Sve metode koje nisu orchestration (`bind`, `singleton`, `boot`, `terminate`) – ukloni.

- [ ] Ako postoje lifecycle metode (`beginScope`, `endScope`) – prebaci ih u `StoreLifecycleStep`.

✅ **Rezultat:** Kernel postaje "pure orchestrator" — bez logike, bez grananja, bez stanja.

---

## 🔩 FAZA 4 — "Pomeranje state-a u KernelContext"

**Cilj:** izbaciti svaku formu stanja iz kernela.

📋 **Taskovi:**

- [ ] Sve kolekcije i mape (`$this->instances`, `$this->scopes`, `$this->metadata`) prebaci u:
  ```php
  $ctx->metadata['scopes'] = [...];
  $ctx->metadata['lifecycle'] = ...;
  ```

- [ ] Dodaj metode u `KernelContext`:
  ```php
  public function set(string $key, mixed $value): void;
  public function get(string $key): mixed;
  public function has(string $key): bool;
  ```

- [ ] Kernel više ne sme da drži state između rezolucija — sve prolazi kroz kontekst.

✅ **Rezultat:** KernelContext = jedini runtime state, sve transijentno.

---

## 🧠 FAZA 5 — "Uvedi ResolutionPipelineBuilder"

**Cilj:** konfiguracija pipeline-a se seli van kernela.

📁 `src/Container/Core/Kernel/ResolutionPipelineBuilder.php`

📋 **Taskovi:**

- [ ] Napravi `ResolutionPipelineBuilder::default()` koji:
  - [ ] sklapa sve step-ove u pravilnom redosledu
  - [ ] injektuje zavisnosti (`engine`, `injector`, `policy`, `scopeManager`, `metrics`)

- [ ] Kernel više ne zna kako se pipeline pravi — samo ga prima u konstruktoru.

✅ **Rezultat:** redosled DI koraka konfigurabilan i testabilan izvan kernela.

---

## 🧩 FAZA 6 — "Error hook & diagnostics refinement"

**Cilj:** obezbediti enterprise-grade kontrolu toka i grešaka.

📋 **Taskovi:**

- [ ] Napravi `ErrorHandlingStep` koji hvata sve greške:
  ```php
  final class ErrorHandlingStep implements KernelStep {
      public function __invoke(KernelContext $ctx): void {
          try { $ctx->next(); }
          catch (\Throwable $e) {
              $ctx->metadata['error'] = $e;
              // opcionalno: rethrow ili log
          }
      }
  }
  ```

- [ ] Dodaj `pipeline_start` timestamp u `KernelContext` pre `run()` poziva.

- [ ] Proširi `CollectDiagnosticsStep` da meri ukupno vreme pipeline-a.

✅ **Rezultat:** kernel pipeline ima audit trail, vreme i error hook.

---

## 🔧 FAZA 7 — "Test reliability"

**Cilj:** potvrditi da je refaktor funkcionalno ekvivalentan starom kodu.

📋 **Taskovi:**

- [ ] Napiši:
  ```
  tests/Kernel/ContainerKernelTest.php
  tests/Kernel/Steps/*.php
  ```

- [ ] Testiraj:
  - [ ] `resolve($id)` vraća očekivanu instancu
  - [ ] svi step-ovi menjaju `KernelContext` ispravno
  - [ ] `ErrorHandlingStep` ne ruši tok
  - [ ] `CollectDiagnosticsStep` beleži pipeline vreme

✅ **Rezultat:** full coverage orchestration layer-a.

---

## 🧹 FAZA 8 — "Legacy cleanup"

**Cilj:** ukloniti redundantni kod i strukture koje Kernel sada preuzima.

📋 **Taskovi:**

- [ ] Premesti stari `ContainerKernel` i `Container.php` u `_legacy/`

- [ ] Obriši `Traits/` folder

- [ ] U `Features/`:
  - [ ] izbaci logiku iz `LifecycleFlow` i `DiagnosticsFlow`
  - [ ] ostavi samo DSL API (`policy()->strict()`, `design()->use()`, itd.)

- [ ] Refaktori Engine da više ne zna za Kernel (`setContainer()` → obrisati).

✅ **Rezultat:** bez dupliranja orkestracije, čista hijerarhija.

---

## 🧩 FAZA 9 — "Documentation & developer ergonomics"

📋 **Taskovi:**

- [ ] Kreiraj `docs/KernelFlow.md`:
  ```
  Analyze → Guard → Resolve → Inject → Invoke → Store → Collect
  ```

- [ ] U README dodaj deo:
  ```
  Architecture Layers:
  Core/Kernel – orchestration
  Engine – resolution logic
  Features – DSL configuration
  Container – PSR-11 façade
  ```

- [ ] Dodaj UML dijagram pipeline-a.

✅ **Rezultat:** dokumentovan, samorazumljiv sistem.

---

## 💎 FAZA 10 — "Validation & polish"

📋 **Taskovi:**

- [ ] Pokreni benchmark – meriti vreme `resolve()` pre i posle

- [ ] Analiziraj memory footprint (KernelContext leak check)

- [ ] Dodaj PSR-14 event dispatch (opciono) za observability

- [ ] Commit i merge u `develop`.

✅ **Rezultat:** production-grade, auditabilan, testabilan Kernel.

---

## 📦 Finalni rezultat

📂 Struktura:

```
Container/
 ├── Core/
 │   ├── Kernel/
 │   │   ├── Contracts/
 │   │   ├── Steps/
 │   │   ├── ResolutionPipeline.php
 │   │   ├── ResolutionPipelineBuilder.php
 │   │   └── ContainerKernel.php
 │   ├── DefinitionStore.php
 │   ├── ScopeManager.php
 │   └── ServiceDefinition.php
 ├── Engine/
 ├── Features/
 ├── Guard/
 ├── Observe/
 ├── Act/
 ├── Think/
 └── Container.php
```

---

🧙‍♂️ **Kratko objašnjenje za malog:**

> Kernel više ne misli, ne odlučuje, ne zna ništa o implementaciji.
> Samo zna: "šta ide posle čega".
> Svaki drugi deo sistema (engine, policy, scope, metrics) živi u stepovima.
> Tvoj kod je sada enterprise DI arhitektura — modularan, jasan, i spreman za rast.

---

*Ovaj TODO plan vodi developera kroz kompletnu transformaciju ContainerKernel-a iz "debela" u "čist" orchestrator. Svaka faza ima jasne taskove i rezultate.*
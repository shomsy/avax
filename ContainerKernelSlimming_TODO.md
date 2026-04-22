# ⚙️ **CONTAINER KERNEL SLIMMING & RESPONSIBILITY SPLIT**

> *Cilj: Pretvoriti postojeći ContainerKernel u tanak, čitljiv orchestration sloj (~200 linija), a sve pomoćne logike
prebaciti u zasebne kernel servise.*

---

## 📁 NOVA STRUKTURA

```
Container/Core/Kernel/
├── ContainerKernel.php        ← orchestration (200-250 linija)
├── KernelState.php            ← držanje flow instanci i cache
├── KernelCompiler.php         ← compile, validate, cache
├── KernelFacade.php           ← public API layer (bind, extend, when)
├── ResolutionPipeline.php
├── ResolutionPipelineBuilder.php
└── Contracts/
    └── KernelContext.php
```

---

## 🧩 FAZA 1 — "Priprema: identifikuj sve odgovornosti u Kernelu"

**Cilj:** označiti trenutne zone odgovornosti u postojećem kodu.

📋 **Zadatak:**

- [ ] Otvori `ContainerKernel.php`
- [ ] Obeleži sledeće metode komentarima:
    - [ ] `// STATE` – sve gde se koriste `$designFlow`, `$policyFlow`, `$diagnosticsFlow`, `$compilationStats`
    - [ ] `// COMPILE` – `compile()`, `validate()`, `clearCache()`, `getCompilationStats()`
    - [ ] `// API` – `bind()`, `singleton()`, `scoped()`, `extend()`, `resolving()`, `when()`, `instance()`
- [ ] Ostavi orchestration metode netaknute:
    - [ ] `get()`, `resolve()`, `call()`, `injectInto()`, `beginScope()`, `endScope()`

✅ **Rezultat:** 3 zone označene u postojećem kodu.

---

## 🧱 FAZA 2 — "Izvuci KernelState"

**Cilj:** izdvojiti state management u zaseban servis.

📁 `src/Container/Core/Kernel/KernelState.php`

```php
<?php
declare(strict_types=1);

namespace Avax\Container\Core\Kernel;

use Avax\Container\Features\Think\Flow\DesignFlow;
use Avax\Container\Features\Operate\Boot\LifecycleFlow;
use Avax\Container\Features\Actions\Advanced\Policy\PolicyFlow;
use Avax\Container\Features\Actions\Advanced\Observe\DiagnosticsFlow;

final class KernelState
{
    public ?DesignFlow $design = null;
    public ?LifecycleFlow $lifecycle = null;
    public ?PolicyFlow $policy = null;
    public ?DiagnosticsFlow $diagnostics = null;
    public ?array $compilationStats = null;
}
```

📋 **Zadatak:**

- [ ] Kreiraj `KernelState.php` sa strukturom iznad
- [ ] U `ContainerKernel`, dodaj:
  ```php
  private KernelState $state;
  ```
- [ ] U konstruktoru:
  ```php
  $this->state = new KernelState();
  ```
- [ ] Zameni sve reference:
  ```php
  // Pre
  $this->designFlow ??= new DesignFlow(...)

  // Posle
  $this->state->design ??= new DesignFlow(...)
  ```
- [ ] Isto uradi za `policyFlow`, `lifecycleFlow`, `diagnosticsFlow`, `compilationStats`

✅ **Rezultat:** flow i cache instanci više nisu deo kernela, već state.

---

## 🧠 FAZA 3 — "Izvuci KernelCompiler"

**Cilj:** izdvojiti compile/validate logiku u poseban servis.

📁 `src/Container/Core/Kernel/KernelCompiler.php`

```php
<?php
declare(strict_types=1);

namespace Avax\Container\Core\Kernel;

use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Features\Think\Prototype\DependencyInjectionPrototypeFactory;
use Avax\Container\Features\Think\Verify\VerifyPrototype;
use Avax\Container\Features\Define\Store\ServiceDefinition;
use Throwable;

final class KernelCompiler
{
    public function __construct(
        private readonly DefinitionStore $definitions,
        private readonly DependencyInjectionPrototypeFactory $prototypeFactory
    ) {}

    public function compile(): array { /* move compile() code */ }

    public function validate(): void { /* move validate() code */ }

    public function clearCache(): void { /* move clearCache() code */ }

    public function stats(?array $compilationStats): array {
        return $compilationStats ?? [
            'compiled_services' => $this->prototypeFactory->getCache()->count(),
            'cache_size'        => $this->prototypeFactory->getCache()->count(),
            'compilation_time'  => 0.0,
            'validation_errors' => 0,
        ];
    }
}
```

📋 **Zadatak:**

- [ ] Kreiraj `KernelCompiler.php` sa strukturom iznad
- [ ] Iseci sledeće metode iz `ContainerKernel`:
    - [ ] `compile()`
    - [ ] `validate()`
    - [ ] `clearCache()`
    - [ ] `getCompilationStats()`
- [ ] Prebaci njihov kod unutar `KernelCompiler` odgovarajućih metoda
- [ ] U `ContainerKernel` dodaj:
  ```php
  private KernelCompiler $compiler;
  ```
- [ ] U konstruktoru:
  ```php
  $this->compiler = new KernelCompiler($this->definitions, $this->prototypeFactory);
  ```
- [ ] Napravi delegatore:
  ```php
  public function compile(): array { return $this->compiler->compile(); }
  public function validate(): self { $this->compiler->validate(); return $this; }
  public function clearCache(): self { $this->compiler->clearCache(); return $this; }
  public function getCompilationStats(): array { return $this->compiler->stats($this->state->compilationStats); }
  ```

✅ **Rezultat:** kernel ne zna više ništa o compile/validate logici.

---

## 🧩 FAZA 4 — "Izvuci KernelFacade"

**Cilj:** izdvojiti public API (bind, extend, when) u poseban servis.

📁 `src/Container/Core/Kernel/KernelFacade.php`

```php
<?php
declare(strict_types=1);

namespace Avax\Container\Core\Kernel;

use Avax\Container\Features\Core\Enum\ServiceLifetime;
use Avax\Container\Features\Define\Store\ServiceDefinition;
use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Features\Define\Bind\BindingBuilder;
use Avax\Container\Features\Define\Bind\ContextBuilder;
use Avax\Container\Features\Operate\Scope\ScopeManager;
use Closure;
use InvalidArgumentException;

final class KernelFacade
{
    public function __construct(
        private readonly DefinitionStore $definitions,
        private readonly ScopeManager $scopes
    ) {}

    public function bind(string $abstract, string|callable|null $concrete = null, ServiceLifetime $lifetime = ServiceLifetime::Transient): BindingBuilder
    { /* move bindAs() + bind() logic */ }

    public function singleton(...) { /* move singleton() */ }

    public function scoped(...) { /* move scoped() */ }

    public function extend(...) { /* move extend() */ }

    public function resolving(...) { /* move resolving() */ }

    public function when(string $consumer): ContextBuilder
    { return new ContextBuilder($this->definitions, $consumer); }

    public function instance(string $abstract, object $instance): void
    { /* move instance() code */ }
}
```

📋 **Zadatak:**

- [ ] Kreiraj `KernelFacade.php` sa strukturom iznad
- [ ] Iseci sledeće metode iz `ContainerKernel`:
    - [ ] `bind()`, `singleton()`, `scoped()`, `extend()`, `resolving()`, `instance()`, `when()`
- [ ] Prebaci njihov kod unutar `KernelFacade` odgovarajućih metoda
- [ ] U `ContainerKernel` dodaj:
  ```php
  private KernelFacade $facade;
  ```
- [ ] U konstruktoru:
  ```php
  $this->facade = new KernelFacade($this->definitions, $this->scopes);
  ```
- [ ] Izloži facade kroz javne metode:
  ```php
  public function bind(string $abstract, string|callable|null $concrete = null): BindingBuilder
  { return $this->facade->bind($abstract, $concrete); }

  // ili
  public function facade(): KernelFacade { return $this->facade; }
  ```

✅ **Rezultat:** kernel više ne zna za binding logiku.

---

## ⚙️ FAZA 5 — "Očisti orchestration sloj"

**Cilj:** svesti ContainerKernel na minimalni, čitljivi orchestration.

📋 **Zadatak:**

- [ ] U `ContainerKernel` **ostavi samo sledeće metode:**
  ```php
  public function __construct(...) {...}
  public function get(string $id)
  public function resolve(ServicePrototype $prototype): mixed
  public function call(callable|string $callable, array $parameters = []): mixed
  public function injectInto(object $target): object
  public function beginScope(): void
  public function endScope(): void
  ```
- [ ] Sve ostalo (compile, bind, validate, design, lifecycle, policy, diagnostics, etc.) – sada delegeriše ili
  KernelState, Compiler ili Facade.

✅ **Rezultat:** `ContainerKernel` postaje tanak orchestration sloj (200–250 linija), čitljiv i održiv.

---

## 🔧 FAZA 6 — "Konačno čišćenje i validacija"

**Cilj:** uveriti se da je refaktor funkcionalan i čist.

📋 **Zadatak:**

- [ ] Pokreni sve postojeće testove (`ContainerKernelTest`, `ResolutionPipelineTest`)
- [ ] Uveri se da sve public metode i dalje rade kroz delegaciju
- [ ] Obriši nepotrebne `use` direktive:
    - [ ] `BindingBuilder`, `ContextBuilder`, `InvalidArgumentException`, itd.
- [ ] Obriši privatne helper-e ako više nisu potrebni:
    - [ ] `bindAs()`, `resolveDefinitionClass()`
- [ ] Commituj refaktor u posebnu granu:
  ```
  git checkout -b feature/kernel-split
  git add .
  git commit -m "refactor: Split ContainerKernel responsibilities

  - Extract KernelState for flow management
  - Extract KernelCompiler for build-time logic
  - Extract KernelFacade for public API
  - ContainerKernel now pure orchestration (~200 lines)"
  ```

✅ **Rezultat:** funkcionalan refaktor, spreman za merge.

---

## 🧠 FAZA 7 — "Documentation i review"

**Cilj:** dokumentovati novu arhitekturu za buduće developere.

📋 **Zadatak:**

- [ ] Kreiraj `docs/KernelArchitecture.md`:
  ```
  ## Container Kernel Architecture

  ### Layered Design
  - **ContainerKernel** — orchestration core (~200 lines)
  - **KernelState** — flow holders & runtime cache
  - **KernelCompiler** — compile/validate subsystem
  - **KernelFacade** — user API layer (bind, extend, when)
  - **ResolutionPipeline** — service resolution steps

  ### Flow
  User API → KernelFacade → DefinitionStore
  Resolution → ContainerKernel → ResolutionPipeline → Steps
  Build-time → KernelCompiler → PrototypeFactory
  Runtime → KernelState → Flow instances
  ```

- [ ] Napiši dijagram (PlantUML ili draw.io):
  ```
  [ContainerKernel] --> [KernelState]
  [ContainerKernel] --> [KernelCompiler]
  [ContainerKernel] --> [KernelFacade]
  [KernelFacade] --> [DefinitionStore]
  [KernelCompiler] --> [PrototypeFactory]
  [ContainerKernel] --> [ResolutionPipeline]
  ```

✅ **Rezultat:** dokumentovana nova arhitektura, spremna za code review.

---

## 🧩 FAZA 8 — "Enterprise polish (optional)"

**Cilj:** dodati enterprise-grade features.

📋 **Zadatak:**

- [ ] Kreiraj `KernelDiagnostics.php`:
  ```php
  final class KernelDiagnostics {
      public function exportMetrics(): string { /* čita $state->diagnostics */ }
      public function logPipelineTiming(): void { /* beleži vreme */ }
  }
  ```

- [ ] U `ResolutionPipelineBuilder`, ubaci `ErrorHandlingStep` kao prvi korak

- [ ] Izbaci `setContainer()` iz `ResolutionEngine` ako više nije potreban

- [ ] Testiraj cold boot + cache warmup scenario

✅ **Rezultat:** enterprise-ready sa observability.

---

## ✅ Očekivani rezultat

| Komponenta        | Broj linija pre | Broj linija posle | Svrha                        |
|-------------------|-----------------|-------------------|------------------------------|
| `ContainerKernel` | ~850            | ~220              | orchestration                |
| `KernelState`     | –               | ~50               | držanje flow-a               |
| `KernelCompiler`  | –               | ~150              | build-time logic             |
| `KernelFacade`    | –               | ~180              | public API                   |
| Ukupno            | ~850            | ~600 raspoređeno  | bolje čitljiv, modularan kod |

---

## 🏁 Završni koraci

- [ ] Kreirati `KernelState.php`
- [ ] Kreirati `KernelCompiler.php`
- [ ] Kreirati `KernelFacade.php`
- [ ] Prebaciti metode po fazama
- [ ] Očistiti orchestration
- [ ] Dodati unit testove
- [ ] Napisati dokumentaciju

---

*Ovaj TODO plan vodi kroz surgical refactor ContainerKernel-a u 4 specijalizovana servisa, održavajući potpunu
funkcionalnost i backward compatibility.*
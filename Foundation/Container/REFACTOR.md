Ovo je finalni plan:

Da. Ovo je finalna verzija koju bih ja potpisao. Čista je, DX-oriented, dovoljno enterprise, ali bez mehaničkog šuma. 🙂

```text id="4b9g2p"
Container/
  Container.php
  ContainerInterface.php

  DependencyInjection/
    CreateContainer.php
    RegisterServices.php
    ResolveService.php
    CallFunction.php
    OpenScope.php
    CloseScope.php
    BootProviders.php

    Registrations/
      ServiceRegistry.php
      ServiceRegistryInterface.php
      ServiceRegistration.php
      RegisterForTarget.php

    Resolution/
      ServiceResolver.php
      ResolveRequest.php
      ResolvePlan.php
      ResolveDependencies.php
      BuildService.php
      CreateServiceBlueprint.php
      BlueprintCache.php
      ServiceBlueprint.php

    Calls/
      FunctionCaller.php
      ResolveCallArguments.php

    Injection/
      InjectProperties.php
      InjectMethods.php
      InjectionReport.php

    Scopes/
      ScopeInterface.php
      ScopeStore.php
      ManageScopes.php
      Lifetimes/
        SharedLifetime.php
        ScopedLifetime.php
        TransientLifetime.php

    Providers/
      ServiceProviderInterface.php

    Configuration/
      CreateContainerConfig.php
      ContainerSettings.php

    Observability/
      ResolutionTimeline.php
      ResolutionMetrics.php
      ResolutionTelemetry.php

    Policies/
      ResolutionPolicy.php

    Errors/
      ContainerException.php

    Foundation/
      Time/
        Clock.php
      Ids/
        IdGenerator.php

  docs/
```

Ovo je dobar finalni rečnik zato što odmah govori:

šta radiš:

* create
* register
* resolve
* call
* open
* close
* boot

šta sistem ima:

* registrations
* resolution
* calls
* injection
* scopes
* providers
* configuration
* observability
* policies
* errors
* foundation

Šta sam namerno izbacio:

* `Flow` folder
* `Core`
* `Features`
* `Actions`
* `Builder`
* `ContextualBinding`
* `ResolutionContext`
* `PrototypeAnalyzer`
* `Manager` kao generički pattern naziv
* `Factory` tamo gde `CreateX` bolje kaže šta se dešava

Kratak zakon za ovu verziju:

Flow entry file je glagol ili glagol + objekat.
Area folder je prosta imenica.
Model file je jasna imenica.
Contract file je eksplicitan interface.
Pattern suffix koristiš samo kad je baš neophodan.

Jedina rezervisana napomena je `ReadServiceBlueprint.php`. Njega ne bih ubacivao unapred. Ubaci ga tek kada stvarno postoji poseban korak čitanja postojećeg blueprint-a iz cache-a ili drugog izvora.

Po meni, ovo je sada već pravi DSL za tvoj container.

# Container refactor TODO

## Goal

Implement the final DX-first Container architecture with this canonical structure:

Container/
  Container.php
  ContainerInterface.php

  DependencyInjection/
    CreateContainer.php
    RegisterServices.php
    ResolveService.php
    CallFunction.php
    OpenScope.php
    CloseScope.php
    BootProviders.php

    Registrations/
      ServiceRegistry.php
      ServiceRegistryInterface.php
      ServiceRegistration.php
      RegisterForTarget.php

    Resolution/
      ServiceResolver.php
      ResolveRequest.php
      ResolvePlan.php
      ResolveDependencies.php
      BuildService.php
      CreateServiceBlueprint.php
      BlueprintCache.php
      ServiceBlueprint.php

    Calls/
      FunctionCaller.php
      ResolveCallArguments.php

    Injection/
      InjectProperties.php
      InjectMethods.php
      InjectionReport.php

    Scopes/
      ScopeInterface.php
      ScopeStore.php
      ManageScopes.php
      Lifetimes/
        SharedLifetime.php
        ScopedLifetime.php
        TransientLifetime.php

    Providers/
      ServiceProviderInterface.php

    Configuration/
      CreateContainerConfig.php
      ContainerSettings.php

    Observability/
      ResolutionTimeline.php
      ResolutionMetrics.php
      ResolutionTelemetry.php

    Policies/
      ResolutionPolicy.php

    Errors/
      ContainerException.php

    Foundation/
      Time/
        Clock.php
      Ids/
        IdGenerator.php

  docs/

---

## Phase 1. Freeze the architecture language

- [ ] Declare this tree as the only canonical structure.
- [ ] Declare `DependencyInjection/` as the system root.
- [ ] Declare that root files under `DependencyInjection/` are flow entry points.
- [ ] Declare that subfolders under `DependencyInjection/` are internal work areas.
- [ ] Ban old vocabulary:
  - [ ] Core
  - [ ] Features
  - [ ] Actions
  - [ ] Think
  - [ ] Operate
  - [ ] Define
  - [ ] Builder
  - [ ] ContextualBinding
  - [ ] Instantiator
  - [ ] PrototypeAnalyzer
  - [ ] generic Manager naming unless truly justified

---

## Phase 2. Create the target folders

- [ ] Create `DependencyInjection/`
- [ ] Create `DependencyInjection/Registrations/`
- [ ] Create `DependencyInjection/Resolution/`
- [ ] Create `DependencyInjection/Calls/`
- [ ] Create `DependencyInjection/Injection/`
- [ ] Create `DependencyInjection/Scopes/`
- [ ] Create `DependencyInjection/Scopes/Lifetimes/`
- [ ] Create `DependencyInjection/Providers/`
- [ ] Create `DependencyInjection/Configuration/`
- [ ] Create `DependencyInjection/Observability/`
- [ ] Create `DependencyInjection/Policies/`
- [ ] Create `DependencyInjection/Errors/`
- [ ] Create `DependencyInjection/Foundation/Time/`
- [ ] Create `DependencyInjection/Foundation/Ids/`

---

## Phase 3. Public surface

### Keep
- [ ] `Container.php`
- [ ] `ContainerInterface.php`

### Refactor
- [ ] Make `Container.php` a thin facade only.
- [ ] Ensure `Container.php` delegates only to:
  - [ ] `CreateContainer`
  - [ ] `RegisterServices`
  - [ ] `ResolveService`
  - [ ] `CallFunction`
  - [ ] `OpenScope`
  - [ ] `CloseScope`
  - [ ] `BootProviders`
- [ ] Remove public logic that does not belong in the facade.

---

## Phase 4. Flow entry points

### Implement / rename flows
- [ ] Create `DependencyInjection/CreateContainer.php`
- [ ] Create `DependencyInjection/RegisterServices.php`
- [ ] Create `DependencyInjection/ResolveService.php`
- [ ] Create `DependencyInjection/CallFunction.php`
- [ ] Create `DependencyInjection/OpenScope.php`
- [ ] Create `DependencyInjection/CloseScope.php`
- [ ] Create `DependencyInjection/BootProviders.php`

### Rules
- [ ] Each flow file must be a clear root owner.
- [ ] Each flow file must expose one obvious responsibility.
- [ ] No flow file should turn into a dumping ground.

---

## Phase 5. Registrations area

### Final files
- [ ] `ServiceRegistry.php`
- [ ] `ServiceRegistryInterface.php`
- [ ] `ServiceRegistration.php`
- [ ] `RegisterForTarget.php`

### Old to new naming intent
- [ ] `RegistryInterface` -> `ServiceRegistryInterface`
- [ ] `BindingRegistry` -> `ServiceRegistry`
- [ ] `BindingDefinition` -> `ServiceRegistration`
- [ ] `ContextualBinding` -> `RegisterForTarget`
- [ ] `BindingBuilder` -> remove or merge into `ServiceRegistration` / registration workflow
- [ ] `ContextualBindingBuilder` -> remove or merge into `RegisterForTarget`

### Functional tasks
- [ ] Define what one service registration contains.
- [ ] Centralize registration storage in `ServiceRegistry`.
- [ ] Make `RegisterForTarget` own target-specific registration rules.
- [ ] Remove any leftover builder-style registration API unless absolutely necessary.

---

## Phase 6. Resolution area

### Final files
- [ ] `ServiceResolver.php`
- [ ] `ResolveRequest.php`
- [ ] `ResolvePlan.php`
- [ ] `ResolveDependencies.php`
- [ ] `BuildService.php`
- [ ] `CreateServiceBlueprint.php`
- [ ] `BlueprintCache.php`
- [ ] `ServiceBlueprint.php`

### Old to new naming intent
- [ ] `ResolutionKernel` -> `ServiceResolver`
- [ ] `ResolutionContext` -> `ResolveRequest`
- [ ] `ResolutionPipeline` -> `ResolvePlan`
- [ ] `DependencyResolver` -> `ResolveDependencies`
- [ ] `Instantiator` -> `BuildService`
- [ ] `PrototypeAnalyzer` -> `CreateServiceBlueprint`
- [ ] `FilePrototypeCache` -> `BlueprintCache`
- [ ] `ServicePrototype` -> `ServiceBlueprint`

### Functional tasks
- [ ] Define the minimal resolve request object.
- [ ] Define what the resolve plan contains.
- [ ] Keep dependency resolution separate from object creation.
- [ ] Keep blueprint creation separate from blueprint caching.
- [ ] Remove any duplicate runtime/state/context classes if they no longer bring clarity.

---

## Phase 7. Calls area

### Final files
- [ ] `FunctionCaller.php`
- [ ] `ResolveCallArguments.php`

### Old to new naming intent
- [ ] `CallableInvoker` -> `FunctionCaller`
- [ ] any invocation executor/context abstractions -> merge or remove unless clearly justified
- [ ] argument resolution logic -> `ResolveCallArguments`

### Functional tasks
- [ ] Make `CallFunction` the public flow entry.
- [ ] Make `FunctionCaller` the internal owner of actual invocation.
- [ ] Keep call-argument resolution explicit and isolated.

---

## Phase 8. Injection area

### Final files
- [ ] `InjectProperties.php`
- [ ] `InjectMethods.php`
- [ ] `InjectionReport.php`

### Tasks
- [ ] Keep property injection separate from method injection.
- [ ] Keep `InjectionReport` only if it provides real value.
- [ ] Remove any extra injection planning classes if they are only mechanical wrappers.

---

## Phase 9. Scopes area

### Final files
- [ ] `ScopeInterface.php`
- [ ] `ScopeStore.php`
- [ ] `ManageScopes.php`
- [ ] `Lifetimes/SharedLifetime.php`
- [ ] `Lifetimes/ScopedLifetime.php`
- [ ] `Lifetimes/TransientLifetime.php`

### Old to new naming intent
- [ ] `ScopeManager` -> `ManageScopes`
- [ ] keep `ScopeStore` if it is real storage
- [ ] keep `ScopeInterface` if it is a real boundary
- [ ] simplify lifetime handling if lifecycle resolver / strategies are overdesigned

### Functional tasks
- [ ] `OpenScope` and `CloseScope` must delegate here cleanly.
- [ ] Keep scope lifecycle explicit.
- [ ] Keep lifetime decisions obvious and local.

---

## Phase 10. Providers area

### Final files
- [ ] `ServiceProviderInterface.php`

### Tasks
- [ ] Keep only the contract in the core container.
- [ ] Remove product-specific providers from the core container package.
- [ ] Make `BootProviders` the single owner of provider lifecycle flow.

---

## Phase 11. Configuration area

### Final files
- [ ] `CreateContainerConfig.php`
- [ ] `ContainerSettings.php`

### Old to new naming intent
- [ ] `ContainerConfig` -> evaluate whether it stays as config object or becomes `CreateContainerConfig`
- [ ] `Settings` -> `ContainerSettings`
- [ ] `ContainerFactory` -> remove if `CreateContainer` is the canonical assembly flow
- [ ] `ContainerBuilder` -> remove unless staged build is truly necessary
- [ ] `KernelConfigFactory` -> remove or merge if redundant

### Functional tasks
- [ ] Define one honest configuration object model.
- [ ] Remove builder/factory duplication.
- [ ] Keep assembly language aligned with `CreateContainer`.

---

## Phase 12. Observability area

### Final files
- [ ] `ResolutionTimeline.php`
- [ ] `ResolutionMetrics.php`
- [ ] `ResolutionTelemetry.php`

### Old to new naming intent
- [ ] `CollectMetrics` -> `ResolutionMetrics`
- [ ] `Telemetry` -> `ResolutionTelemetry`

### Tasks
- [ ] Keep only observability that is actually useful.
- [ ] Do not overbuild telemetry abstractions.
- [ ] Tie observability to real resolution behavior.

---

## Phase 13. Policies area

### Final files
- [ ] `ResolutionPolicy.php`

### Tasks
- [ ] Keep only one policy language.
- [ ] Avoid generic `ContainerPolicy` if it actually governs resolution behavior.
- [ ] Ensure this file owns explicit policy decisions, not random validation leftovers.

---

## Phase 14. Errors area

### Final files
- [ ] `ContainerException.php`

### Tasks
- [ ] Keep a clean error boundary.
- [ ] Add specialized exceptions only if they improve clarity.
- [ ] Avoid exception explosion.

---

## Phase 15. Foundation area

### Final files
- [ ] `Foundation/Time/Clock.php`
- [ ] `Foundation/Ids/IdGenerator.php`

### Tasks
- [ ] Keep foundation tiny.
- [ ] Do not move domain logic here.
- [ ] Do not create a helper bucket.

---

## Phase 16. Remove over-technical patterns

Review and simplify these aggressively:

- [ ] Builder classes
- [ ] Factory classes
- [ ] Runtime classes
- [ ] State classes
- [ ] Context classes
- [ ] Executor classes
- [ ] Analyzer classes
- [ ] Strategy layers
- [ ] Facades behind facades

### Rule
- [ ] If a class exists only to preserve a pattern name, remove it.
- [ ] If two classes can merge without losing clarity, merge them.
- [ ] If a flow file can directly own the logic, prefer that over another wrapper.

---

## Phase 17. Namespace convergence

- [ ] Make one canonical namespace strategy.
- [ ] Remove old namespace truth completely.
- [ ] No parallel namespace styles may survive.

Example direction:

- [ ] `Avax\Container\DependencyInjection\...` as canonical
- [ ] remove legacy `Core/*`, `Features/*`, or split vocabulary namespaces

---

## Phase 18. Tests

### Target test mirror
- [ ] `tests/DependencyInjection/CreateContainer/`
- [ ] `tests/DependencyInjection/RegisterServices/`
- [ ] `tests/DependencyInjection/ResolveService/`
- [ ] `tests/DependencyInjection/CallFunction/`
- [ ] `tests/DependencyInjection/OpenScope/`
- [ ] `tests/DependencyInjection/CloseScope/`
- [ ] `tests/DependencyInjection/BootProviders/`

- [ ] `tests/DependencyInjection/Registrations/`
- [ ] `tests/DependencyInjection/Resolution/`
- [ ] `tests/DependencyInjection/Calls/`
- [ ] `tests/DependencyInjection/Injection/`
- [ ] `tests/DependencyInjection/Scopes/`
- [ ] `tests/DependencyInjection/Providers/`
- [ ] `tests/DependencyInjection/Configuration/`
- [ ] `tests/DependencyInjection/Observability/`
- [ ] `tests/DependencyInjection/Policies/`
- [ ] `tests/DependencyInjection/Foundation/`

### Tasks
- [ ] Align all test naming with the final tree.
- [ ] Remove tests that refer to legacy names.
- [ ] Add missing tests for the new root flows.

---

## Phase 19. Docs

- [ ] Rewrite docs to teach only the final DX-first vocabulary.
- [ ] Remove old docs language around:
  - [ ] Core
  - [ ] Features
  - [ ] Actions
  - [ ] Think
  - [ ] Operate
  - [ ] ContextualBinding
  - [ ] Builder-heavy language

### Docs must explain:
- [ ] what the user does with the container
- [ ] what the system does internally
- [ ] how registration works
- [ ] how resolution works
- [ ] how scope lifecycle works
- [ ] how providers boot
- [ ] what configuration means
- [ ] what observability is exposed

---

## Phase 20. Legacy cleanup

- [ ] Delete all old folders once the new structure is live.
- [ ] Delete legacy wrappers that duplicate the new owners.
- [ ] Delete dead compatibility layers.
- [ ] Delete old docs that describe the wrong architecture.

---

## Definition of Done

- [ ] One canonical tree exists.
- [ ] One canonical namespace exists.
- [ ] One canonical vocabulary exists.
- [ ] No legacy architectural shape remains.
- [ ] Public facade is thin.
- [ ] Flow entry files are obvious.
- [ ] Internal folders are DX-first and human-readable.
- [ ] Over-technical names are gone.
- [ ] Tests mirror the final structure.
- [ ] Docs explain the final mental model only.
- [ ] The container reads like a system you use, not like a framework anatomy diagram.

Da. Evo ti **rename map + plan za brisanje legacy** za ovu finalnu DX verziju containera.

Po onome što se sada vidi u kodu, nova arhitektura je već delimično živa: `Container` je tanak public facade i već delegira na `RegisterBindings`, `ResolveService`, `InvokeCallable`, `BeginScope` i `EndScope`, dok `BootProviders` već postoji kao poseban lifecycle flow. Ali i dalje postoje stari nazivi i paralelna istina kroz `BindingBuilderInterface`, `ContextBuilderInterface`, `Registrar`, `PrototypeAnalyzer`, `InvokeAction`, `CollectMetrics`, `ContainerBuilder` i stare `Core/*` docs tragove.    

Zato bih radio ovako.

## 1. Finalni canonical tree

```text
Container/
  Container.php
  ContainerInterface.php

  DependencyInjection/
    CreateContainer.php
    RegisterServices.php
    ResolveService.php
    CallFunction.php
    OpenScope.php
    CloseScope.php
    BootProviders.php

    Registrations/
      ServiceRegistry.php
      ServiceRegistryInterface.php
      ServiceRegistration.php
      RegisterForTarget.php

    Resolution/
      ServiceResolver.php
      ResolveRequest.php
      ResolvePlan.php
      ResolveDependencies.php
      BuildService.php
      CreateServiceBlueprint.php
      BlueprintCache.php
      ServiceBlueprint.php

    Calls/
      FunctionCaller.php
      ResolveCallArguments.php

    Injection/
      InjectProperties.php
      InjectMethods.php
      InjectionReport.php

    Scopes/
      ScopeInterface.php
      ScopeStore.php
      ManageScopes.php
      Lifetimes/
        SharedLifetime.php
        ScopedLifetime.php
        TransientLifetime.php

    Providers/
      ServiceProviderInterface.php

    Configuration/
      CreateContainerConfig.php
      ContainerSettings.php

    Observability/
      ResolutionTimeline.php
      ResolutionMetrics.php
      ResolutionTelemetry.php

    Policies/
      ResolutionPolicy.php

    Errors/
      ContainerException.php

    Foundation/
      Time/
        Clock.php
      Ids/
        IdGenerator.php

  docs/
```

Ovo je u skladu sa onim što si već počeo da radiš: root flow entry fajlovi pričaju šta korisnik radi sa containerom, a unutrašnje lane-ove pričaju koje oblasti rada sistem ima. To je znatno bliže tvom DSL-u od trenutnog mehaničkog vocabulary-ja.  

## 2. Old → new rename map

Ovo je konkretan rename plan, file po file.

### Public facade i top-level flows

```text
DependencyInjection/Flows/RegisterBindings/RegisterBindings.php
  -> DependencyInjection/RegisterServices.php

DependencyInjection/Flows/ResolveService/ResolveService.php
  -> DependencyInjection/ResolveService.php

DependencyInjection/Flows/InvokeCallable/InvokeCallable.php
  -> DependencyInjection/CallFunction.php

DependencyInjection/Flows/BeginScope/BeginScope.php
  -> DependencyInjection/OpenScope.php

DependencyInjection/Flows/EndScope/EndScope.php
  -> DependencyInjection/CloseScope.php

DependencyInjection/Flows/BootProviders/BootProviders.php
  -> DependencyInjection/BootProviders.php

DependencyInjection/Configuration/ContainerBuilder.php
  -> DependencyInjection/CreateContainer.php
```

Zašto: flow entry treba da bude direktno ispod `DependencyInjection/`, bez dodatnog `Flows/` hodnika, jer ti je flow već očigledan iz imena fajla. `ContainerBuilder` trenutno realno radi assembly entry posao, pa je bliži `CreateContainer` nego pattern-heavy `Builder`.  

### Registrations

```text
RegistryInterface.php / BindingBuilderInterface.php / ContextBuilderInterface.php
  -> premestiti pod DependencyInjection/Registrations/

DependencyInjection/Capabilities/Definitions/Contracts/RegistryInterface.php
  -> DependencyInjection/Registrations/ServiceRegistryInterface.php

DependencyInjection/Capabilities/Definitions/Store/DefinitionStore.php
  -> DependencyInjection/Registrations/ServiceRegistry.php

DependencyInjection/Capabilities/Definitions/Store/ServiceDefinition.php
  -> DependencyInjection/Registrations/ServiceRegistration.php

DependencyInjection/Capabilities/Definitions/Bindings/Registrar.php
  -> DELETE after logic moves into ServiceRegistry or rename to ServiceRegistry internals

ContextBuilder.php / ContextBuilderInterface.php
  -> DependencyInjection/Registrations/RegisterForTarget.php
```

Zašto: `RegistryInterface`, `BindingBuilderInterface` i `ContextBuilderInterface` su previše tehnički i previše low-level za finalni DX jezik, a `RegisterBindings` trenutno upravo izbacuje te tipove. To treba da se prevede u jednostavnije: registry, registration i register-for-target jezik.  

### Resolution

```text
DependencyInjection/Capabilities/Resolution/Kernel/ContainerKernel.php
  -> DependencyInjection/Resolution/ServiceResolver.php

DependencyInjection/Capabilities/Resolution/Kernel/KernelContext.php
  -> DependencyInjection/Resolution/ResolveRequest.php

DependencyInjection/Capabilities/Resolution/Pipeline/ResolutionPipelineFactory.php or ResolutionPipeline.php
  -> DependencyInjection/Resolution/ResolvePlan.php

DependencyInjection/Capabilities/Resolution/Engine/DependencyResolver.php
  -> DependencyInjection/Resolution/ResolveDependencies.php

DependencyInjection/Capabilities/Resolution/Engine/Instantiator.php
  -> DependencyInjection/Resolution/BuildService.php

DependencyInjection/Capabilities/Prototypes/Analyze/PrototypeAnalyzer.php
  -> DependencyInjection/Resolution/CreateServiceBlueprint.php

DependencyInjection/Capabilities/Prototypes/Cache/FilePrototypeCache.php
  -> DependencyInjection/Resolution/BlueprintCache.php

DependencyInjection/Capabilities/Prototypes/Model/ServicePrototype.php
  -> DependencyInjection/Resolution/ServiceBlueprint.php
```

Zašto: ovi nazivi trenutno pričaju unutrašnju anatomiju engine-a, ne ono što se dešava. A dokumentacija već jasno opisuje resolution kao glavni sistemski tok. DX jezik je ovde jači ako kaže: resolve request, resolve plan, resolve dependencies, build service, create blueprint.  

### Calls

```text
DependencyInjection/Capabilities/Invocation/InvokeAction.php
  -> DependencyInjection/Calls/FunctionCaller.php

ResolveMethodParameters.php / callable argument logic
  -> DependencyInjection/Calls/ResolveCallArguments.php
```

Zašto: `InvokeAction` je stari framework-ish naziv. `FunctionCaller` i `ResolveCallArguments` mnogo bolje pričaju šta sistem radi kad ga koristiš. `Container::call()` već pokazuje da je ovo stvarno public behavior. 

### Injection

```text
DependencyInjection/Capabilities/Injection/InjectDependencies.php
  -> DELETE or merge into InjectProperties/InjectMethods if only orchestration wrapper

DependencyInjection/Capabilities/Injection/Properties/PropertyInjector.php
  -> DependencyInjection/Injection/InjectProperties.php

DependencyInjection/Capabilities/Injection/Methods/MethodInjector.php
  -> DependencyInjection/Injection/InjectMethods.php

DependencyInjection/Capabilities/Injection/Reports/InjectionReport.php
  -> DependencyInjection/Injection/InjectionReport.php
```

Zašto: `InjectDependencies` često završi kao mehanički wrapper. Ako ne nosi jedinstvenu semantiku, spoji ga. Ostavi samo ono što je jasno: property injection, method injection, injection report. `Container::inspectInjection()` već jasno troši `InjectionReport`. 

### Scopes

```text
ScopeManagerInterface.php / ScopeManager.php
  -> DependencyInjection/Scopes/ManageScopes.php

ScopeRegistry.php
  -> DependencyInjection/Scopes/ScopeStore.php

ScopeInterface.php
  -> DependencyInjection/Scopes/ScopeInterface.php

LifecycleResolver.php / strategy registry / scoped/singleton/transient strategies
  -> DependencyInjection/Scopes/Lifetimes/SharedLifetime.php
  -> DependencyInjection/Scopes/Lifetimes/ScopedLifetime.php
  -> DependencyInjection/Scopes/Lifetimes/TransientLifetime.php
```

Zašto: `ManageScopes` je u tvom DSL-u mnogo čistije od `ScopeManager`, a `ScopeStore` je DX jasniji od `ScopeRegistry` ako ta klasa stvarno čuva stanje scope-a. Stari `Core/Kernel/Strategies/*` vocabulary treba da umre i da se prevede u jednostavne lifetime file-ove. 

### Providers

```text
DependencyInjection/Capabilities/Providers/Contracts/ServiceProviderInterface.php
  -> DependencyInjection/Providers/ServiceProviderInterface.php
```

Zašto: provider contract ostaje, ali sve product-specific providere izbaci iz core DI engine-a. `BootProviders` je dovoljan lifecycle flow. 

### Configuration

```text
DependencyInjection/Configuration/ContainerConfig.php
  -> DependencyInjection/Configuration/CreateContainerConfig.php

Config/Settings.php or Configuration/Settings.php
  -> DependencyInjection/Configuration/ContainerSettings.php

DependencyInjection/Configuration/ContainerBuilder.php
  -> DELETE after CreateContainer is canonical
```

Zašto: ako zadržavaš `CreateContainer.php` kao glavni assembly flow, onda i config vocabulary treba da priča isti jezik. `ContainerBuilder` je pattern-heavy i semantički slabiji od `CreateContainer`. `Settings` je previše generički, `ContainerSettings` je pošteniji.  

### Observability

```text
DependencyInjection/Capabilities/Observability/Timeline/ResolutionTimeline.php
  -> DependencyInjection/Observability/ResolutionTimeline.php

DependencyInjection/Capabilities/Observability/Metrics/CollectMetrics.php
  -> DependencyInjection/Observability/ResolutionMetrics.php

DependencyInjection/Capabilities/Observability/Telemetry/Telemetry.php
  -> DependencyInjection/Observability/ResolutionTelemetry.php
```

Zašto: `CollectMetrics` i `Telemetry` su previše generički. `ResolutionMetrics` i `ResolutionTelemetry` zatvaraju vocabulary sa `ResolutionTimeline`. 

### Policies

```text
DependencyInjection/Capabilities/Policies/ContainerPolicy.php
  -> DependencyInjection/Policies/ResolutionPolicy.php

DependencyInjection/Capabilities/Policies/StrictResolutionPolicy.php
  -> DELETE or merge into ResolutionPolicy if strictness is just one mode, not a first-class type
```

Zašto: trenutni policy je realno vezan za resolution ponašanje, ne za “ceo container” kao mutan pojam. A `StrictResolutionPolicy` je verovatno previše granularan kao zaseban top-level tip osim ako stvarno ima više policy implementacija koje žive ravnopravno. 

### Errors i foundation

```text
DependencyInjection/Capabilities/Resolution/Errors/ContainerException.php
  -> DependencyInjection/Errors/ContainerException.php

DependencyInjection/Foundation/Time/Clock.php
  -> KEEP

DependencyInjection/Foundation/Ids/IdGenerator.php
  -> KEEP
```

## 3. Plan za brisanje legacy

Radi ovo tek kad novi file i novi namespace rade.

### Obriši odmah kada zamena postoji

```text
Core/
Features/
Actions/
Think/
Operate/
Define/
Guard/
Observe/   (ako je old root, ne novi finalni Observe/Observability lane)
Tools/
```

Stari docs i audit već jasno pokazuju da ti legacy shape i dalje živi u dokumentaciji kroz `docs/Core/*`, `Core/Kernel/*`, `Features/*` vocabulary. To mora da umre čim novi tree postane source of truth.   

### Obriši kada se logika upije ili preimenuje

```text
BindingBuilderInterface.php
ContextBuilderInterface.php
ContextBuilder.php
Registrar.php
PrototypeAnalyzer.php
ReflectionTypeAnalyzer.php   (review: keep only if really needed internally, not as DSL surface)
Instantiator.php
InvokeAction.php
CollectMetrics.php
Telemetry.php
ContainerBuilder.php
KernelConfigFactory.php
KernelRuntime.php
KernelState.php
KernelFacade.php
ResolutionEngine.php / EngineInterface.php   (if they collapse into simpler DX units)
ScopeRegistry.php
LifecycleResolver.php
*Strategy.php files from old Core/Kernel/Strategies/
```

Ovde nemoj sentimentalno čuvati pattern-ima motivisane tipove ako mogu da se utope u jednostavniji jezik. `ContainerBuilder`, `Instantiator`, `PrototypeAnalyzer`, `InvokeAction`, `CollectMetrics` su baš primeri mehaničkog vocabulary-ja koji ti sada više ne treba.  

### Review manually pre brisanja

```text
ContainerKernel.php
KernelContext.php
ResolutionPipelineFactory.php / ResolutionPipeline.php
DependencyResolver.php
PropertyInjector.php
MethodInjector.php
ServicePrototypeFactory.php
RuntimeContainer.php
```

Ove stvari su možda realne interne odgovornosti, ali ne moraju preživeti pod istim imenom. Za svaku od njih pitaj:

* da li ima jedinstvenu odgovornost
* da li novi DX naziv može da je zameni
* da li dve ili tri od njih mogu da se spoje bez gubitka jasnoće

## 4. Bezbedan redosled brisanja

Prvo preimenuj i premesti, pa tek onda briši.

1. Napravi novi finalni tree.
2. Preimenuj flow entry fajlove.
3. Preimenuj registrations/resolution/calls/injection/scopes/configuration/observability/policies file-ove.
4. Promeni namespace-e i `use` reference.
5. Pusti testove i autoload refresh.
6. Tek tada obriši legacy foldere i stare docs reference.
7. Na kraju obriši prazne foldere i stare audit fajlove koji opisuju pogrešan shape.

## 5. Minimalni shell plan

Ovo nije kompletan script, ali ti daje radni redosled.

```bash
mkdir -p DependencyInjection/{Registrations,Resolution,Calls,Injection,Scopes/Lifetimes,Providers,Configuration,Observability,Policies,Errors,Foundation/Time,Foundation/Ids}

# rename / move examples
mv DependencyInjection/Flows/RegisterBindings/RegisterBindings.php DependencyInjection/RegisterServices.php
mv DependencyInjection/Flows/ResolveService/ResolveService.php DependencyInjection/ResolveService.php
mv DependencyInjection/Flows/InvokeCallable/InvokeCallable.php DependencyInjection/CallFunction.php
mv DependencyInjection/Flows/BeginScope/BeginScope.php DependencyInjection/OpenScope.php
mv DependencyInjection/Flows/EndScope/EndScope.php DependencyInjection/CloseScope.php
mv DependencyInjection/Flows/BootProviders/BootProviders.php DependencyInjection/BootProviders.php

# configuration
mv DependencyInjection/Configuration/ContainerConfig.php DependencyInjection/Configuration/CreateContainerConfig.php
mv Config/Settings.php DependencyInjection/Configuration/ContainerSettings.php

# after code and imports are fixed
rm -rf Core Features Actions Think Operate Define Guard Tools
rm -rf DependencyInjection/Flows
```

Nemoj `rm -rf` dok ne poravnaš namespace-e i testove.

## 6. Definition of done za legacy cleanup

Legacy je stvarno ugašen tek kad važi sve ovo:

* ne postoji nijedan import ka `Core/*`, `Features/*`, `Actions/*`, `Think/*`, `Operate/*`, `Define/*`
* ne postoji nijedan public file sa starim mehaničkim vocabulary-jem ako postoji nova DX zamena
* docs više nigde ne objašnjavaju sistem kroz `Core` i `Features`
* test tree prati finalni tree
* `Container` ostaje tanak i delegira samo na novi rečnik
* postoji samo jedan namespace story
* ne postoje dve istine za isti koncept

## 7. Najkraći operativni ToDo

Ako hoćeš brutalno kratko:

1. Zaključaj finalni DX tree.
2. Uradi old→new rename map iznad.
3. Poravnaj namespace i imports.
4. Ukloni builder/context/analyzer/executor vocabulary gde je suvišan.
5. Ugasi `Core/*` i stari docs jezik.
6. Pusti testove.
7. Obriši legacy folder-e.



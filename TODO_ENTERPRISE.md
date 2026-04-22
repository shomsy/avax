# Avax Container Enterprise-Grade Roadmap (To-Do Plan)

## 1) DEPENDENCYINJECTOR - Trait Refactor Stabilization

Cilj: dovrsiti trait-based modularizaciju i dodati testove i izolovane policy kontrole.

Kratkorocno:

- [ ] Testiraj svaki trait pojedinacno
  Napravi `tests/Traits/Provides*.php` za:
    - ProvidesBinding
    - ProvidesConditionalBinding
    - ProvidesLazyBinding
    - ProvidesLifecycle
    - ProvidesDiagnostics
    - ProvidesStrictModes

  Svaki trait treba da ima barem 2 test metode: "binds correctly" i "throws expected error".

- [ ] U DependencyInjector dodaj orchestration layer za resolve() i inject()
    - resolve() bira strategiju (ResolveFromBindings, ResolveFromAutowire, ResolveFromLazyInstances,
      ResolveFromConditional).
    - inject() bira injektor (ConstructorInjector, MethodInjector, PropertyInjector).

- [ ] Prosiri trait ProvidesTelemetry
    - Metode: recordBindingHit(), recordLazyLoad(), recordResolutionTime().
    - Skladisti u Validate/Telemetry/MetricsCollector.

## 2) RESOLVER & INJECTION - Intelligent Orchestration

Cilj: napraviti smart strategy sistem koji automatski bira kako i odakle se zavisnost resava.

Kratkorocno:

- [ ] U Resolver/Resolver.php implementiraj Strategy Chain Pattern:

```php
foreach ($this->strategies as $strategy) {
    if ($strategy->supports($id)) return $strategy->resolve($id);
}
throw new ResolutionException($id);
```

- [ ] Registruj strategije u DependencyInjector::__construct():
    - ResolveFromBindings
    - ResolveFromConditional
    - ResolveFromAutowire
    - ResolveFromLazyInstances

- [ ] U Injection/InjectionManager.php napravi InjectionStrategyChain za:
    - ConstructorInjector
    - PropertyInjector
    - MethodInjector

- [ ] Dodaj test DependencyInjectorStrategyTest.php:
    - Testira prioritet: Binding > Conditional > Autowire > Lazy.

## 3) APPLICATION - Bootstrap & Provider Robustness

Cilj: ojacati boot pipeline i modularnu inicijalizaciju.

Kratkorocno:

- [ ] Provider modular grouping (vec uradjeno) - dodaj lazy booting:

```php
if ($provider->isDeferred()) $this->deferred[] = $provider;
```

- [ ] Bootstrap validation
  U Bootstrapper dodaj:

```php
if (!$appPath->exists('config/bootstrap.php')) {
    throw new BootstrapException('Missing bootstrap config.');
}
```

- [ ] ErrorHandler upgrade
    - Dodaj handle(Throwable $e, string $context = 'general').
    - Dodaj opcionalni TelemetryReporterInterface za log forwarding.

- [ ] AppEnvironment upgrade
    - Dodaj: env(), mode(), isTesting(), name() helper metode.
    - Automatski prepoznaj environment preko .env, APP_ENV, i php_sapi_name().

## 4) BEHAVIOR & POLICY - Enforcement & Safety

Cilj: policy i lifecycle logiku dovesti na nivo framework guard rails.

Kratkorocno:

- [ ] ContainerPolicy enrichment
    - Dodaj strictAutowire, lazyFallback, allowRebinding, trackMetrics.
    - U BootstrapConfiguration::policy() mapiraj sve ENV promenljive (CONTAINER_STRICT_AUTOWIRE,
      CONTAINER_LAZY_FALLBACK...).

- [ ] Lifecycle logging
    - Svaki event (boot, resolved, shutdown) neka emituje log sa timestampom u Validate/Telemetry.

- [ ] CircularGuard
    - Dodaj metodu getCircularTrace() koja vraca JSON trace umesto Exception-only.

## 5) VALIDATE & TELEMETRY - Observability

Cilj: izgraditi introspektivni sloj koji container cini self-aware.

Kratkorocno:

- [ ] Napravi Validate/Diagnostics/ContainerHealthReport.php
    - Skuplja: broj bindings, lazy proxies, strict mode, autowire hits, errors.
    - Generise JSON report.

```bash
php bin/container container:health
```

- [ ] Dodaj MetricsCollector::flushToPrometheus()
    - Mapira container metrike (resolve time, hit rate, lazy count).

- [ ] Napravi Validate/Validator.php koji koristi sve iznad i vraca ValidationReport.

## 6) SECURITY & CONFIG - Stability & Isolation

Cilj: uciniti container sigurnim, predvidivim i idempotentnim.

Kratkorocno:

- [ ] Config/services.php - dodaj hash check: ako promenjen, invalidiraj compiled cache.
- [ ] ContainerCompiler.php - koristi atomic writes sa tempnam() + rename() (ako nije vec).
- [ ] DependencyInjector - dodaj assertNoGlobalState() da proveri da nista nije registrovano van policy-a.
- [ ] SessionServiceProvider - podeli ga na tri:
    - SessionStorageProvider
    - SessionSecurityProvider
    - SessionLifecycleProvider

## 7) TESTS - Full Coverage & Regression Safety

Cilj: garantovana stabilnost kod svake izmene.

Kratkorocno:

- [ ] Kreiraj tests/Integration/ContainerLifecycleTest.php
    - Testira bootstrap -> policy -> provider load -> shutdown.

- [ ] Kreiraj tests/Unit/DependencyInjectorTraitsTest.php
    - Jedan test po trait-u (isolation test).

- [ ] Dodaj tests/Integration/ContainerHealthTest.php
    - Koristi container:validate --report=json i proverava JSON strukturu.

- [ ] Integracija u CI:

```yaml
phpunit:
  script: vendor/bin/phpunit --colors=always --coverage-text
```

## 8) DOCS & RELEASE

Cilj: da kod postane samodokumentovan i spreman za open source.

Kratkorocno:

- [ ] ARCHITECTURE.md - azuriraj dijagram i dodaj trait-mapu.
- [ ] CHANGELOG.md - dodaj sekciju:

```
[v1.2] DependencyInjector traits finalized, added telemetry & health reports.
```

- [ ] README.md - ubaci sekciju Fluent API Overview.
- [ ] docs/container.md - dodaj real-world examples:

```php
$service = $container->resolve(FooService::class);
$container->inject($service);
```

- [ ] Regenerisi Container.txt i commituj ga samo lokalno (gitignore aktivan).

## 9) LONG-TERM (Optional)

Vision: Avax Container v2.0 (Enterprise Kernel)

| Tema                    | Opis                                                      |
|-------------------------|-----------------------------------------------------------|
| Async Container         | Dodaj FiberLoop i resolveAsync() (vec postoji stub)       |
| Distributed Metrics     | Eksport u Prometheus / OpenTelemetry                      |
| Container CLI           | container:profile, container:graph, container:policy      |
| Service Graph Explorer  | Generisi graf svih bindings (Graphviz export)             |
| Multi-tenant Containers | Scoped ContainerContext sa izolovanim binding store-ovima |
| Reflection Cache Warmup | CLI komanda za pre-kompajliranje reflection meta-podataka |

## TL;DR za malog

1) Zavrsiti i testirati trait-ove.
2) Dodati orchestration layer u resolver i injection.
3) Ojancati Application/Provider sistem (deferred, error handling, bootstrap guards).
4) Dodati Telemetry i HealthReport modul.
5) Povecati test coverage (trait + integration).
6) Ocistiti docs i generisati Container.txt.

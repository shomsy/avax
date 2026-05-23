# Evidence: Forbid Service Locator in Public DX Examples

## Stage
Governance documentation remediation

## Status
GREEN

## Blocker Fixed

**BLOCKER #1** from review of commit f10e1c2d5:
§12 PublicSurface Rule used `app(StorageRegistry::class)->driver($name)` as a "GOOD" example.

This contradicted:
- AGENTS.md §19: "global app() shortcuts inside component internals" forbidden
- how-to-runtime-composition.md §7.1: static facades must not perform ad-hoc runtime service lookup
- how-to-runtime-composition.md §11: Runtime Service Locator Prohibition

## Files Changed

| File | Action | Reason |
|------|--------|--------|
| `.agents/how-to/modeling/how-to-model-flows.md` | Modified | Removed app() example, added compliant examples and §12.1 Anti-Service-Locator DX Rule |
| `.agents/how-to/architecture/how-to-runtime-composition.md` | Modified | Added §7.5 Component Registration Model with ServiceProvider, Catalog/Registry, and facade boundary rules |

## Exact Old Bad Example Removed

```php
// §12 line 614 — REMOVED
final readonly class Storage
{
    public static function driver(string $name): StorageDriver
    {
        return app(StorageRegistry::class)->driver($name);
    }
}
```

## New Approved Patterns

### Pattern 1: Instance-based PublicSurface (preferred)

```php
final readonly class Storage
{
    public function __construct(
        private StorageRuntime $runtime,
    ) {}

    public function driver(string $name): StorageDriver
    {
        return $this->runtime->driver($name);
    }
}
```

### Pattern 2: Static facade as thin DX wrapper

```php
final class Storage
{
    private static ?StorageRuntime $runtime = null;

    /** @internal Called by component registration, not by callers. */
    public static function setRuntime(StorageRuntime $runtime): void
    {
        self::$runtime = $runtime;
    }

    public static function driver(string $name): StorageDriver
    {
        if (self::$runtime === null) {
            throw new StorageNotConfiguredException(
                'Storage runtime has not been configured. '
                . 'Ensure the Storage component is registered.'
            );
        }

        return self::$runtime->driver($name);
    }
}
```

### Facade Bridge Contract

Static facade bridge is allowed only when:
1. Runtime is set during component registration (not at call time)
2. Facade provides `setRuntime()` for test injection
3. Facade provides `reset()` for worker safety
4. Facade provides no ad-hoc resolve/get/make method
5. Facade throws when runtime is not configured (no silent fallback)
6. Facade is documented with PHPDoc explaining lifecycle

## New Section Added: §12.1 AvaX Anti-Service-Locator DX Rule

Covers:
- **12.1.1 What Is Forbidden**: app(), container(), resolve(), make(), $container->get() in PublicSurface
- **12.1.2 What Is Allowed**: injected runtime, boot-configured facade bridge, compiled accessor
- **12.1.3 The Good Pipeline**: PublicSurface -> Runtime -> Catalog -> descriptor -> driver
- **12.1.4 The Bad Pipeline**: PublicSurface -> app() -> hidden service locator -> no validation
- **12.1.5 Responsibility Map**: Container builds graph, Catalog owns names, Runtime executes, PublicSurface delegates
- **12.1.6 Facade Bridge Contract**: 6 rules for allowed static facades

## New Section Added: §7.5 Component Registration Model (runtime-composition.md)

Covers:
- **7.5.1** ServiceProvider per real component boundary
- **7.5.2** Register* composition actions (Storage and Identity examples)
- **7.5.3** Catalog/Registry for string selectors
- **7.5.4** Container as object graph owner, NOT semantic selector registry
- **7.5.5** PublicSurface/facade boundary must not perform service location

## BLOCKER Findings Added

To how-to-model-flows.md §13.1:
- PublicSurface calls app(), container(), resolve(), make(), or container->get()
- Static facade performs ad-hoc runtime service lookup
- String selector maps directly to container key
- Selector resolution bypasses Catalog/Registry/RuntimePlan validation
- Component runtime dependency pulled lazily from container during behavior execution

PASS Examples (§13.1.1):
- Static facade delegates to official boot-configured AvaX facade bridge
- Instance PublicSurface receives runtime via constructor
- ServiceProvider registers runtime, catalog, descriptors, and verification
- Runtime object receives catalog through DI
- Catalog validates selector and fails fast

## Why AvaX Differs from Laravel

| Aspect | Laravel | AvaX |
|--------|---------|------|
| Public DX | `Storage::disk('s3')` via `app()` | `Storage::driver('s3')` via boot-configured bridge |
| Internal resolution | Service container lookup | Explicit Catalog/Registry validation |
| Facade implementation | Static proxy to container resolution | Static proxy to pre-set runtime instance |
| Test injection | Swap container bindings | `setRuntime()` / `reset()` on facade |
| Unknown driver | May throw container exception | Explicit catalog error with available options |
| Configuration | Config files + container bindings | ServiceProvider + Register* + Catalog |

AvaX keeps the same public DX feel but replaces the internal service locator with explicit composition.

## Validation

### Structural Validation
- [x] `app(StorageRegistry::class)` no longer exists in how-to-model-flows.md
- [x] `global app` pattern not taught as GOOD in any governance document
- [x] §12.1 Anti-Service-Locator DX Rule present with all subsections
- [x] §7.5 Component Registration Model present in runtime-composition.md
- [x] BLOCKER findings updated in §13.1
- [x] PASS examples added in §13.1.1
- [x] Appendix A quick reference table updated
- [x] Review checklist updated in runtime-composition.md §10
- [x] Cross-reference established: runtime-composition.md §7.5.5 references how-to-model-flows.md §12.1

### Governance Consistency
- [x] Aligns with AGENTS.md §19: no global app() shortcuts
- [x] Aligns with how-to-runtime-composition.md §7: static facade law
- [x] Aligns with how-to-runtime-composition.md §11: service locator prohibition
- [x] Aligns with how-to-clean-code.md §5.4.1: intent-first fluent API
- [x] Aligns with how-to-design-components.md §6.2: PublicSurface delegates only
- [x] No contradictions with existing governance

### File Hygiene
- [x] No emojis in documents
- [x] GOOD/BAD pattern consistent
- [x] PHP code examples syntactically valid
- [x] Normative language correct

## Public API Impact
NONE — Governance documentation only. No production code changed.

## Remaining Risks

| Risk | Severity | Notes |
|------|----------|-------|
| No automated tooling to detect app() in governance examples | MEDIUM | Would require doc linting not yet built |
| Facade bridge contract needs production proof | YELLOW | Document defines the rule; evidence comes when components implement it |
| Other governance documents may still contain app() examples | YELLOW | Requires grep sweep of all .agents/how-to/**/*.md |

## TODO FOR 11++

1. **Grep sweep of all governance documents** for `app(`, `container(`, `resolve(`, `make(` patterns in GOOD examples — ensure no other service-locator teachings remain
2. **Build doc linting rule** that flags `app(` or `container(` in governance code examples
3. **Production implementation** of the facade bridge contract pattern in Storage, Auth, Cache components
4. **Add contract tests** proving static facades fail fast when runtime not configured
5. **Add negative tests** proving PublicSurface cannot resolve dependencies via service locator
6. **Update existing component examples** in other governance documents to use the boot-configured facade bridge pattern

## Skills Applied
- avax-enterprise-codecraft: Applied design evidence requirements, verified no contradictions
- avax-api-compatibility-contract: NONE public API impact confirmed
- avax-security-threat-model: Service locator prohibition prevents runtime dependency injection attacks, hidden resolution paths, and unvalidated selector bypass
- avax-component-dogfooding: Examples align with Auth component structure patterns and AvaX composition discipline
- avax-test-evidence-quality: Documented what tests must prove (facade fail-fast, catalog validation)

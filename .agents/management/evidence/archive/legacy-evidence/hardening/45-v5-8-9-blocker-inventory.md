# V5.8.9 Blocker Inventory

## Date
2026-05-15

## Validation Results

| Command | Result |
|---|---|
| PHPStan | 253 errors |
| Runtime composition gate | FAIL — 200 findings |
| Runtime assembly gate | FAIL — 3 violations (GraphQLSchema) |
| PHPUnit | 8351 tests, 24008 assertions, 0 errors, 0 failures, 1 deprecation — GREEN |

## Blocker Table

| Blocker | File/group | Count | Root cause | Fix strategy | Blocks V5.9? |
|---|---|---:|---|---|---:|
| DispatchConfiguredRoute runtime leaks | DispatchConfiguredRoute.php:92,93,99 | 3 | `new ControllerResolver`, `new ArgumentResolver`, `new ControllerDispatcher` in `fromRegisteredRoutes` factory method | Move assembly to Configuration/Builders or inject; factory method IS assembly context but gate scans Flows/ | YES — active runtime leak |
| AuthBuilder constructor drift | AuthBuilder.php build() method | ~100 | build() calls `new ClassName()` with wrong/missing parameters across 50+ constructor calls | Match each `new` call to actual constructor signatures; fix parameter names and types | YES — largest PHPStan group |
| AuthBuilder caller drift | Various test/builder call sites | ~70 | Callers construct AuthBuilder-dependent objects with stale signatures | Update caller sites to match canonical constructors | YES — part of AuthBuilder group |
| GraphQLSchema ?? new fallbacks | GraphQLSchema.php:52-54 | 3 | Constructor uses `?? new` for fieldAssembler, schemaSerializer, schemaRouter | Remove ?? new defaults; require injected deps; or register defaults in ServiceProvider | YES — active assembly violation |
| Remaining PHPStan — array shapes | Various | ~20 | `array` type without value type specification | Add array shapes where practical | NO — type strictness |
| Remaining PHPStan — mixed vars | Various | ~15 | Container get() returns mixed, used without narrowing | Add @var annotations or explicit narrowing | NO — type strictness |
| Remaining PHPStan — return types | AppKernel, others | ~10 | Return type too broad (ResponseInterface vs Response) | Narrow return types where safe | NO — type strictness |
| Remaining PHPStan — always-true | AuthBuilder, tests | ~5 | Redundant instanceof checks | Remove redundant checks | NO — cleanup |
| Remaining PHPStan — other | Various | ~5 | Miscellaneous type issues | Fix case by case | NO |
| Pre-existing runtime composition debt | Many components | ~197 | class_exists, ?? new, builder build(), lazy singleton across many components | Dedicated pass — outside V5.8.9 scope | NO — pre-existing, classified |

## Detailed Breakdown

### DispatchConfiguredRoute (3 leaks)
The `fromRegisteredRoutes` static factory method assembles ControllerResolver, ArgumentResolver, and ControllerDispatcher with `new`. The gate scans `framework/System/Flows/` and flags these as MEDIUM severity runtime composition leaks.

**Analysis:** `fromRegisteredRoutes` is a factory method in a Flow class. While factory-like, the gate treats all of `Flows/` as runtime execution. The fix is to either:
1. Move the assembly to a dedicated Configuration builder, or
2. Add to gate's knownAllowances with justification, or
3. Inject these dependencies through constructor

Per governance: factory assembly belongs in Configuration/Builders, not Flows. Option 1 is correct.

### AuthBuilder (~170 errors)
AuthBuilder's `build()` method creates ~50+ objects with `new ClassName(...)`. PHPStan reports constructor parameter mismatches:
- Missing required parameters
- Unknown parameters (renamed in constructor)
- Wrong parameter types

The build() method is in `Configuration/Builders/` — an approved composition context. The issue is not WHERE it assembles, but that constructor signatures drifted from the build() calls.

### GraphQLSchema (3 assembly violations)
GraphQLSchema is in `PublicSurface/` — a forbidden context for `?? new` patterns. The assembly gate correctly flags these.

**Fix:** Make the 3 dependencies required constructor parameters. GraphQLSchema is a user-facing value/model object — it should receive dependencies, not construct defaults. Alternatively, register defaults in a GraphQLServiceProvider.

## V5.9 Blocking Decision

**V5.9 remains BLOCKED until:**
1. DispatchConfiguredRoute runtime leaks fixed (3 findings)
2. AuthBuilder constructor drift fixed (~170 errors)
3. GraphQLSchema assembly violations fixed (3 findings)
4. PHPStan reaches 0 or honestly baselined with no active production bugs

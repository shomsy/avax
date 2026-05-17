# V5.8.8 PHPStan Error Inventory

## Date
2026-05-15

## Initial Count
308 errors (before any V5.8.8 fixes)

## Final Count After This Pass
253 errors (55 fixed)

## Error Groups

| Group ID | Category | Count Before | Count After | Example File | Example Error | Root Cause | Fix Strategy | Blocks V5.9? |
|---|---|---:|---:|---|---|---|---|---:|
| G1 | Missing BuildResponse classes | 12 | 0 | ResponseServiceProvider.php | Class BuildJsonResponse not found | ServiceProvider registered non-existent legacy classes | Removed registrations | NO |
| G2 | CreateResponse wrong namespace | 4 | 0 | CreateResponse.php | Call to static method on unknown class BuildResponse | Wrong import path for BuildResponse | Fixed namespace + rewrote to use CreateHttpResponse | NO |
| G3 | Constructor signature drift (Router) | 4 | 0 | RouterBuilder.php, HttpBuilder.php | NormalizeControllerResult constructor needs CreateHttpResponse | Builders didn't pass required CreateHttpResponse | Added CreateHttpResponse to constructors | NO |
| G4 | Constructor signature drift (ServiceProvider) | 2 | 0 | HttpRouterServiceProvider.php | BuildErrorResponse/NormalizeControllerResult need CreateHttpResponse | Service provider didn't resolve dependency | Added container resolution | NO |
| G5 | Missing AppKernel param | 1 | 0 | HttpServiceProvider.php | Missing parameter createHttpResponse | AppKernel registration didn't match constructor | Fixed registration | NO |
| G6 | HttpFailureBoundaryMiddleware param | 1 | 0 | AppKernel.php | Constructor invoked with 1 parameter, 2 required | Missing CreateHttpResponse in middleware construction | Added CreateHttpResponse | NO |
| G7 | Cache namespace drift | 3 | 0 | RegisterCacheDependencies.php, CacheRegistrar.php | CacheConfiguration class not found in Builders namespace | Missing use imports for Builders classes | Added imports | NO |
| G8 | Cache undefined properties | 3 | 0 | BuildCache.php | Access to undefined property refreshPolicy | BuildCache accessed non-existent CacheConfiguration properties | Removed references to non-existent properties | NO |
| G9 | Cache nullable directory | 1 | 0 | CacheServiceProvider.php | Expects string, string\|null given | Closure didn't narrow nullable property | Captured non-null local variable | NO |
| G10 | DateTime instantiation | 4 | 0 | RegisterDateTimeDependencies.php, RegisterDateTimeServices.php | Cannot instantiate interface Timezone, Clock has no constructor | Wrong instantiation pattern for static facade + interface | Use UtcTimezone + Clock static methods | NO |
| G11 | Always-true test assertions | 3 | 0 | ResponseServiceProviderTest.php, ParallelismProofTest.php | assertInstanceOf always evaluates to true | Redundant assertions on known types | Removed redundant assertions | NO |
| G12 | Mixed variable in tests | 21 | 0 | ResponseServiceProviderTest.php | Mixed variable in $responses->...() | Container get() returns mixed | Added @var annotations | NO |
| G13 | AuthBuilder constructor drift | ~170 | ~170 | AuthBuilder.php | Missing/unknown parameters across 40+ constructor calls | AuthBuilder out of sync with capability constructor signatures | Requires systematic update of all constructor calls | YES |
| G14 | Missing array type hints | ~20 | ~20 | CreateHttpResponse.php, Responses.php | No value type specified in iterable type array | Style/strictness, not functional bug | Add array shapes | NO |
| G15 | Return type specificity | ~10 | ~10 | AppKernel.php, Responses.php | Provide more specific return type Response over ResponseInterface | Style/strictness | Narrow return types | NO |
| G16 | Mixed variable production | ~15 | ~15 | RegisterDatabaseDependencies.php, shortcuts.php | Mixed variable from container get() | Container returns mixed; runtime code uses without narrowing | Add type narrowing or @var annotations | NO |
| G17 | Array value type hints | ~15 | ~15 | Various builders | No value type specified in iterable type array | Style/strictness | Add array shapes | NO |
| G18 | Always-true AuthBuilder instanceof | 3 | 3 | AuthBuilder.php | Instanceof will always evaluate to true | Redundant type checks | Remove redundant checks | NO |
| G19 | Ignored error pattern mismatch | 1 | 1 | Responses.php | ignoreErrors pattern expected 1 time, occurred 8 times | PHPStan config out of sync with actual error frequency | Update phpstan.neon | NO |

## Classification Summary

| Classification | Count |
|---|---:|
| REAL_PRODUCTION_TYPE_BUG | 0 (all constructor drift fixed) |
| CONSTRUCTOR_SIGNATURE_DRIFT | ~170 (AuthBuilder only) |
| ARRAY_SHAPE_MISSING | ~35 |
| MIXED_TYPE_NEEDS_NARROWING | ~15 |
| RETURN_TYPE_MISMATCH | ~10 |
| STALE_TEST_AFTER_VALID_REFACTOR | 0 (all fixed) |
| FALSE_POSITIVE | 1 (ignoreErrors pattern) |
| MISSING_CLASS_REAL_BUG | 0 (all fixed) |

## V5.9 Blocking Decision

**AuthBuilder constructor drift (G13) blocks V5.9** because:
- AuthBuilder is the canonical assembly point for the Auth component
- 170 constructor mismatches mean the builder cannot be trusted to wire Auth correctly
- Runtime behavior depends on correct DI assembly
- Target: Auth component owner must fix in V5.9 or earlier

**All other remaining groups do NOT block V5.9** — they are type strictness/style issues, not functional bugs.

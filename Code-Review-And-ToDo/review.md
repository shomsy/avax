# GOVERNANCE INVENTORY

| file path | applies? | used in this iteration |
| --- | --- | --- |
| `AI Prompts/how-to-architecture.md` | Yes | Yes |
| `AI Prompts/how-to-architecture-extension.md` | Yes | Yes |
| `AI Prompts/how-to-clean-code.md` | Yes | Yes |
| `AI Prompts/how-to-code-style.md` | Yes | Yes |
| `AI Prompts/how-to-coding-standards.md` | Yes | Yes |
| `AI Prompts/how-to-document.md` | Yes | Yes |
| `AI Prompts/how-to-unit-test.md` | Yes | Yes |
| `AI Prompts/how-to-code-review.md` | Yes | Yes |

# GOVERNANCE COMPLIANCE REPORT

| governance source | rule | status | evidence | note |
| --- | --- | --- | --- | --- |
| `how-to-architecture.md` | framework lifecycle owner must be explicit | Pass | `framework/System/...` | new framework axis created |
| `how-to-arhitecture-extension.md` | `PublicSurface/` must delegate | Pass | `PublicSurface/Avax.php`, `Http/HttpKernel.php`, `Console/ConsoleKernel.php` | no real behavior buried in public surface |
| `how-to-clean-code.md` | small safe refactor steps | Pass | new slice added without deleting old component trees | no big-bang move |
| `how-to-code-style.md` | named arguments and imports | Pass | new framework files | local style follows repository convention |
| `how-to-coding-standards.md` | modern PHP, explicit failure, strict types | Pass | all new PHP files | runtime and failure types explicit |
| `how-to-document.md` | docs mirror new source folders | Pass | `docs/framework/System/...` | framework/System ownership folders now have `how-this-works.md` coverage |
| `how-to-unit-test.md` | behavior-first tests | Pass | `tests/Unit/Framework`, `tests/Feature/Framework`, `tests/Contract/Runtime`, `tests/Integration/Framework/HandleIncomingHttpIntegrationTest.php` | framework slice now proves callback HTTP and route-backed HTTP separately |
| `how-to-code-review.md` | findings and risks must be explicit | Pass | this file, `risk-register.md` | blockers are recorded directly |

# GOVERNANCE FINDINGS

## Finding 1: Legacy HTTP response component required namespace normalization before safe reuse

- Severity: Resolved
- Evidence: `components/HTTP/Response/...`
- Symptom: framework test run originally hit a fatal redeclaration caused by namespace drift inside the response capability tree
- Why it mattered: the new framework flow could not safely reuse that component until the namespace map was repaired
- Action taken: normalized response component namespaces and imports, fixed response-specific tests, and switched `HandleIncomingHttp` back to `Avax\HTTP\Response\ResponseFactory`

## Finding 2: Existing bootstrap path is not a valid lifecycle owner

- Severity: High
- Evidence: `bootstrap/bootstrap.php`
- Symptom: bootstrap imports an `AppFactory` that does not exist in the current tree
- Why it matters: old bootstrap cannot be treated as a reliable production owner for the migration
- Action taken: `framework/System` now boots independently through `PublicSurface/Avax.php`

## Finding 3: Namespace drift still exists across legacy components

- Severity: High
- Evidence: mixed `Avax\\...` and `components\\...` declarations under `components/`
- Why it matters: reuse is possible only behind explicit adapters until those trees are normalized
- Action taken: composer autoload now maps both prefixes and the new framework slice isolates legacy coupling

## Finding 4: Full suite parser debt was reduced, but broader legacy namespace drift remains outside the framework slice

- Severity: High
- Evidence: `tests/Foundation/...`, `tests/Integration/...`, `components/compat.php`
- Symptom: the repository used to fail in parser/type-compatibility phase before meaningful tests could start
- Why it matters: a migration cannot be called safe if basic verification dies before behavior is exercised
- Action taken: normalized duplicate imports across legacy tests, added explicit compatibility aliases for the smallest safe HTTP/request/router bridge, and corrected facade namespaces
- Remaining gap: full `phpunit` now advances past parser noise but still hits wider legacy namespace drift in non-migrated component trees such as DataHandling

## Finding 5: Worker-safe runtime lifecycle was missing from the original framework slice

- Severity: Resolved
- Evidence: `framework/System/Capabilities/Runtime/Worker/*`, `framework/System/Flows/HandleWorkerRequest/*`, `framework/System/PublicSurface/Runtime/*`
- Symptom: the first framework slice could boot and serve one HTTP request, but had no canonical worker boundary for repeated requests or shutdown bookkeeping
- Why it mattered: the migration plan explicitly requires request-scope reset safety for long-lived runtimes before adapter isolation can be trusted
- Action taken: added worker runtime contracts, generic worker loop, repeated-request flow, shutdown flow, public runtime kernel, first-party adapter shells, runtime leak checker, and contract tests proving no request-scope leakage across two worker requests

## Finding 6: Framework HTTP no longer stops at a raw callback boundary

- Severity: Resolved
- Evidence: `framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php`, `MatchHttpRoute.php`, `RunHttpRoute.php`, `ConfiguredRoutesHttpHandler.php`, `tests/Integration/Framework/HandleIncomingHttpIntegrationTest.php`
- Symptom: the earlier framework HTTP path only executed one framework-level callback and never proved that the existing request and router capabilities could run behind `RuntimeRequest`
- Why it mattered: phase 5 explicitly requires request and router reuse behind canonical framework owners, otherwise the new public HTTP surface would stay structurally shallow and migration would stall
- Action taken: added explicit bridge owners that translate `RuntimeRequest` into the existing request shape, register routes through the existing router builders, match routes through the existing matcher, dispatch actions through the existing controller dispatcher, and load the real `Presentation/HTTP/routes/web.routes.php` through a temporary facade container boundary

## Finding 7: Static analysis still sees namespace debt inside the reused HTTP/request/router bridge

- Severity: High
- Evidence: targeted `phpstan analyse framework/System/Flows/HandleIncomingHttp ...`
- Symptom: runtime behavior and integration tests now pass, but PHPStan still reports unknown-class and dual-namespace issues for reused legacy request/router dependencies
- Why it matters: the bridge is production-executable now, but the legacy `Avax\\` / `components\\` split still weakens static trust and keeps the migration incomplete
- Action taken: kept the runtime bridge explicit and narrow in `components/compat.php` instead of widening alias sprawl
- Remaining gap: the affected request/router subtrees need canonical namespace normalization so static analysis can evaluate the bridge honestly without relying on runtime aliases

# GOVERNANCE EXCEPTIONS

1. The full `AI Prompts` corpus was not copied verbatim under `docs/governance/` in this iteration.
   - Reason: the authoritative prompt files already exist and copying them wholesale would create duplicate sources during a structural migration.
   - Control: `docs/governance/README.md` and mirror pages explicitly point to the authoritative files.

2. Targeted framework PHPUnit runs still surface PHPUnit 10.5 / PHP 8.5 runner deprecation noise.
   - Reason: the current vendor stack emits internal deprecation notices unrelated to response behavior.
   - Control: response behavior is still covered; the remaining noise is verification-environment debt, not a framework flow blocker.

3. `components/compat.php` currently aliases only the smallest safe subset of legacy HTTP/router classes.
   - Reason: broader eager aliasing reaches inconsistent legacy trees and can break autoload itself.
   - Control: compatibility bridges stay explicit and narrow until the affected component slices are migrated properly.

4. Targeted PHPStan for the new request/router bridge still fails on reused legacy dual-namespace owners.
   - Reason: runtime aliases make the bridge executable, but static discovery still sees split ownership across `Avax\\...` and `components\\...` classes.
   - Control: the bridge is test-covered and kept narrow; the follow-up work is canonical namespace normalization, not wider alias masking.

# GOVERNANCE COVERAGE SUMMARY

- phases covered in this iteration: `0` through `10`, with `11` started through legacy owner removal
- new framework code added: lifecycle owner, runtime abstractions, request scope, state reset, HTTP flow, request/router bridge owners, console flow, worker runtime loop, shutdown flow, runtime public surface, runtime adapter shells, docs validators, runtime leak checker
- documentation status: `framework/System` mirror is complete for current ownership folders and validator scripts pass
- legacy verification cleanup: duplicate import/parser blockers removed from the test tree so full-suite failures now surface real namespace/runtime debt
- targeted HTTP bridge result: pass for `HandleIncomingHttpIntegrationTest`, `HttpApplicationFeatureTest`, and `HandleIncomingHttpTest`, with the existing PHPUnit coverage warning and PHPUnit-internal PHP 8.5 deprecation noise
- highest unresolved severity: High

# DECISIONS-LOG

1. `framework/System` was introduced without moving existing component trees.
2. existing code is reused where it is structurally safe today:
   - command catalog metadata from `components/Commands/CommandDefinitions.php`
   - `components/HTTP/Response` through `Avax\HTTP\Response\ResponseFactory`
   - narrow compatibility aliases in `components/compat.php` for request/router/response types that still straddle old and new namespace axes
   - route registration, route definitions, route matching, and controller dispatch through explicit framework bridge owners
3. composer autoload was corrected before any framework tests could run.
4. full `phpunit` is no longer blocked by broad parser noise; the next blockers are deeper unmigrated component contracts.
5. `components/Avax.php` was removed because `framework/System/PublicSurface/Avax.php` is now the canonical lifecycle owner.
6. repo-level quality tooling now targets the live migration slice instead of the removed legacy `Foundation/` root.

# NEXT STEPS

1. stabilize a reusable container/application boot path from the existing container code
2. migrate `Middleware` and container-backed controller resolution into the canonical framework HTTP flow so the new bridge stops deferring those concerns
3. migrate the real console execution component so framework CLI stops at metadata reuse and gains production command execution wiring
4. resolve the next full-suite blocker chain in non-migrated DataHandling and HTTP legacy trees
5. normalize the reused request/router namespaces so PHPStan can verify the bridge without runtime alias crutches
6. replace or upgrade the current Rector toolchain, which still fails under the installed PHP 8.5 runtime before framework-specific rules even run

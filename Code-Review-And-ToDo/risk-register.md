# Migration Risk Register

## Active risks

### R1. Composer autoload drift

- Severity: High
- Evidence: `composer.json` previously pointed to missing `Foundation/`
- Mitigation: switched autoload to `components/` and added `Avax\\Framework\\` for `framework/`

### R2. Mixed namespaces inside existing components

- Severity: High
- Evidence: several files under `components/HTTP` and `components/Container` declare `components\\...` namespaces
- Mitigation: new framework slice isolates this behind runtime abstractions and records the cleanup as next migration work

### R3. Legacy bootstrap references missing classes

- Severity: High
- Evidence: `bootstrap/bootstrap.php` imports `AppFactory` that does not exist in the current autoloaded tree
- Mitigation: new `framework/System` boot path exists independently and is test-covered

### R4. Existing response component was stabilized, but adjacent component namespace drift remains

- Severity: Medium
- Evidence: `components/HTTP/Response` now works under `Avax\\HTTP\\Response`, while neighboring component trees still mix `Avax\\...` and `components\\...`
- Mitigation: response is now reused directly by `framework/System`; next cleanup should target the next migrated component slices

### R5. Legacy command catalog compiles, but command execution wiring is incomplete

- Severity: Medium
- Evidence: metadata is stable, but several command classes still point at mixed namespaces
- Mitigation: framework console flow reuses catalog metadata for visibility and runs framework-registered commands directly

### R6. Full test suite still depends on non-migrated legacy namespace surfaces outside the framework slice

- Severity: High
- Evidence: `tests/Foundation/DataHandling/DataTransfer/DataTransferRuntimeTest.php`, `tests/Foundation/Container/...`, legacy HTTP/router tests
- Mitigation: parser and duplicate-import debt has been removed, and narrow compatibility aliases were introduced; the next step is targeted migration of the remaining component contracts instead of wider alias sprawl

### R7. Composer metadata drift still fails validation despite runtime slice improvements

- Severity: Medium
- Evidence: `composer validate --no-check-publish`
- Mitigation: autoload is correct and regenerated, but `composer.json` and `composer.lock` still need synchronized dev-tool constraints in a dedicated dependency pass

### R8. Route-backed framework HTTP is executable, but static analysis still sees split request/router ownership

- Severity: High
- Evidence: targeted `phpstan analyse framework/System/Flows/HandleIncomingHttp ...`
- Mitigation: runtime aliases stay explicit and narrow for execution safety, while the next migration slice must normalize the reused request/router namespaces instead of expanding alias coverage further

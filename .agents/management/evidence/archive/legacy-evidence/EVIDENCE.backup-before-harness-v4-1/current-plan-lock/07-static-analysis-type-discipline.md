# 07 — Static Analysis Type Discipline

**Date:** 2026-05-12
**Branch:** main
**Commit:** 36949b7ac25a67412251cac172d1fb36d29b86c6
**Scope:** PHPStan full-scope analysis

## PHPStan Results

**Before this pass:** 0 errors (framework, components, tests, labs/SystemDesignKit)
**After this pass:** 0 errors (framework, components, tests, labs/SystemDesignKit)

**Command:**
`vendor/bin/phpstan analyse framework components tests labs/SystemDesignKit --memory-limit=1G --error-format=raw --no-progress`

**Output:** (empty — clean)

## Suppressions Inventory

| Location                                   | Suppression                                         | Classification                                  |
|--------------------------------------------|-----------------------------------------------------|-------------------------------------------------|
| RunReactHttpServer.php:143                 | `@phpstan-ignore-next-line`                         | ALLOWED — PSR response set via try/catch branch |
| GenerateOpenApiFromRoutes.php:57           | `@phpstan-ignore-next-line`                         | ALLOWED — complex route data shape              |
| CreateDataObject.php:259,312               | `@phpstan-ignore-next-line`                         | ALLOWED — dynamic property access               |
| GlobalErrorHandler.php:165                 | `@phpstan-ignore deadCode.unreachable`              | ALLOWED — intentional safety code               |
| HttpStatusCode.php:91,99,115,135,143       | `@phpstan-ignore-line`                              | ALLOWED — enum range narrowing                  |
| SessionCookieWriter.php:26,38              | `@phpstan-ignore-line`                              | ALLOWED — setcookie options shape               |
| PdoSessionRegistry.php ×12                 | `@noinspection SqlNoDataSourceInspection`           | ALLOWED — IDE SQL hints                         |
| V4RuntimeAndWarmSafetyTest.php:195,199,209 | `@phpstan-ignore trueAlwaysUsedInAssert/alwaysTrue` | ALLOWED — state proof assertions                |
| SchemaFacadeSqliteTest.php:38,129          | `@phpstan-ignore method.nonObject`                  | ALLOWED — PDO test fixture                      |
| SerializeStructureTest.php:71              | `@phpstan-ignore-next-line`                         | ALLOWED — dynamic serialization test            |

## Deferrals

None. All existing suppressions are classified as ALLOWED with justification.
No new suppressions added. No baselines added. No ignores weakened.

## Conclusion

PHPStan: **GREEN** — 0 issues for full scope.

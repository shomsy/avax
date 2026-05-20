# Threat Analysis

## Threats Mitigated

| Threat | Severity | Before | After |
|--------|----------|--------|-------|
| Arbitrary PHP file execution via seeder filesystem scan | HIGH | Any *Seeder.php file loaded and instantiated | Only classes extending Seeder are accepted |
| Arbitrary class instantiation via seed() parameter | MEDIUM | Any class-string passed to seed() instantiated | Only Seeder subclasses accepted |
| Arbitrary class instantiation via Seeder::call() | MEDIUM | Any class-string passed to call() instantiated | Only Seeder subclasses accepted |
| Arbitrary class instantiation via ProviderRegistry::register() | MEDIUM | Any providerClass string instantiated | Only BaseRegisterDependency subclasses accepted |

## Residual Risks

- Container guarded sites (class_exists only, no interface check): Configuration-controlled, accept as-is
- Container is inherently a generic class resolver — interface check would break its fundamental purpose

# ServiceProvider Contradiction Cleanup

## Summary

All ServiceProvider rules are now consistent across all documents.

## Resolution Table

| File | Old wording | Risk | New wording | Fixed? |
|---|---|---|---|---:|
| `how-to-dependency-injection.md:302` | `Every ACTIVE component MUST have exactly one ServiceProvider.` | Could be read as applying to ROADMAP/SCAFFOLD/LABS components | `Every ACTIVE production component with runtime behavior, public API, dependencies, replaceable services, state, I/O, configuration, or lifecycle ownership MUST have exactly one real ServiceProvider.` + exempt statuses | ✅ |
| `how-to-dependency-injection.md:249` (canonical §4.0) | Already correct | N/A | No change needed | ✅ |
| `how-to-coding-standards.md:1157` | Fixed in previous pass | N/A | Already correct | ✅ |

## Verification

No document now contains broad "every component" wording for ServiceProviders.

# Identity Target Architecture API Compatibility

## Public API Changed

NO breaking public API change in this slice.

## Preserved

- `Identity::auth()`
- `Identity::access()`
- `Identity::credentials()`
- `Identity::tokens()`
- `Identity::tenancy()`
- `Identity::risk()`
- `Identity::externalIdentity()`
- `AttributeCondition::withinHours(int $startHour, int $endHour)` remains callable with the old two-argument form.

## Extended

- `AttributeCondition::withinHours()` now accepts an optional `DateTimeImmutable $currentTime` for deterministic context-based evaluation.
- `IdentityServiceProvider` now registers `IdentityConfiguration` and root `IdentityRuntime` assembly.

## Classification

PATCH_SAFE for this bounded slice.

## Contract Tests

Updated:

- `tests/Unit/Components/Identity/System/IdentitySystemCapabilitiesTest.php`
- `tests/Unit/Components/Identity/Access/PolicyCharacterizationTest.php`

Runtime execution is NOT_PROVEN in this environment because PHP/PHPUnit are blocked by Docker socket access.

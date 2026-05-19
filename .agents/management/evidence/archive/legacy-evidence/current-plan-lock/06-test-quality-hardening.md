# 06 — Test Quality Hardening

**Date:** 2026-05-12
**Branch:** main
**Commit:** 36949b7ac25a67412251cac172d1fb36d29b86c6
**Scope:** Weak assertion audit across all tests

## Weak Assertion Scan

| Pattern                            | Count   | Action                                                                     |
|------------------------------------|---------|----------------------------------------------------------------------------|
| `assertTrue(true)`                 | 6       | 3 fixed (SpanTest), 3 classified ALLOWED (void-return no-exception proofs) |
| `assertFalse(false)`               | 0       | None found                                                                 |
| `assertNotNull`                    | Present | Used legitimately where null-check is meaningful                           |
| `assertInstanceOf`                 | Present | Used legitimately for type contract verification                           |
| "dummy"/"placeholder"/"fake green" | 0       | None found in test descriptions                                            |

## Fixes Applied

### SpanTest (3 fixes, +14 assertions)

1. `it_overwrites_existing_attribute`: `assertTrue(true)` → `assertSame('second', $exported['attributes']['key'])`
    - Now proves attribute overwrite behavior via export.

2. `it_records_exception_without_throwing`: `assertTrue(true)` → assert event count, type, and message
    - Now proves exception was actually recorded with correct data.

3. `it_handles_attribute_with_various_types`: `assertTrue(true)` → assert each type is stored correctly
    - Now proves all 6 attribute types (string, int, float, bool, null, array) survive round-trip.

### Classified ALLOWED (no change)

4. `EventBusTest::it_does_nothing_when_no_handlers_registered` — void return, no-exception proof
5. `MessageBusTest::it_does_nothing_when_publishing_unregistered_event` — void return, no-exception proof
6. `EnforceBackpressureTest::testAllowsWorkBelowThreshold` — void return, no-exception proof

## Result

- Tests before: 7899 tests, 22821 assertions
- Tests after: 7899 tests, 22835 assertions (+14 meaningful assertions)
- No test count decreased
- No tests deleted
- All tests pass

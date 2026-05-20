# Test Proof — TODO-018 Security Logging & Redaction

## Tests Added

| Test File | Tests | Purpose |
|---|---|---|
| CryptographyTest.php (expanded) | 9 | Negative crypto tests: wrong key, tampered ciphertext/tag, invalid key length, base64 roundtrip |
| CryptographySecurityTest.php | 4 | Security tests: wrong encrypter key, empty ciphertext, IV uniqueness, short key rejection |
| RequestSigningSecurityTest.php | 6 | Signing tests: roundtrip, tampered body/method, expired signature, replayed nonce, missing headers |

## Test Coverage

| Behavior | Proven |
|---|---|
| `#[SensitiveParameter]` on encryption key | YES — attribute present on constructor params |
| `#[SensitiveParameter]` on request-signing key | YES — attribute present on constructor params |
| Tampered ciphertext rejected | YES — RuntimeException thrown |
| Wrong key decryption rejected | YES — RuntimeException thrown |
| Tampered auth tag rejected | YES |
| Expired signature rejected | YES |
| Replayed nonce rejected | YES |
| Tampered body/method rejected | YES |
| IV uniqueness | YES |
| Short key rejection | YES |

## Validation Output

| Command | Result |
|---|---|
| `vendor/bin/phpunit --no-coverage tests/Unit/Components/Security/Cryptography/` | PASS (13 tests, 24 assertions) |
| `vendor/bin/phpunit --no-coverage tests/Unit/Framework/Security/RequestSigning/` | PASS (6 tests, 9 assertions) |
| `php tooling/refactor/check-public-surface.php` | PASS |
| `php tooling/refactor/check-runtime-leaks.php` | PASS |

## Note

Focused validation only. Full suite not run. Pre-existing 12 test failures in ProcessPoolParallelismProofTest are unrelated.

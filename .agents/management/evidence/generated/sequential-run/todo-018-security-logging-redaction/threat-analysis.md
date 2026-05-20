# Threat Analysis — Security Logging & Redaction (TODO-018)

## Scope
- `#[SensitiveParameter]` hardening on encryption key material and request-signing secret keys
- Negative cryptographic tests (tampered ciphertext, wrong key, expired signature, replayed nonce)
- No logging infrastructure changes (scope limited to parameter redaction attribute)

## Assets Protected
| Asset | Location | Risk |
|---|---|---|
| Encryption key material | `EncryptionKey::__construct()`, `EncryptionKey::fromBase64()`, `AesEncrypter::__construct()` | Key exposure in stack traces, logs, error dumps |
| Request-signing secret key | `SignInternalRequest::__construct()`, `VerifyInternalRequestSignature::__construct()` | Secret key exposure enabling forged internal requests |

## Threat Model
| Threat | Vector | Existing Control | Added Control |
|---|---|---|---|
| Key material leaked in stack trace | Uncaught exception during encryption/decryption | None | `#[SensitiveParameter]` redacts constructor args in backtraces |
| Secret key leaked in error log | PHP warning/error with backtrace | None | `#[SensitiveParameter]` redacts constructor args in backtraces |
| Tampered ciphertext decrypts to garbage data | Attacker modifies encrypted payload | None (GCM authentication via openssl_decrypt) | Negative test proves tampered ciphertext throws RuntimeException |
| Wrong key decrypts to garbage data | Misconfigured key rotation | None | Negative test proves wrong-key decryption throws RuntimeException |
| Replayed request-signing nonce | Attacker captures and replays signed request | NonceStore replay detection | Test proves replayed nonce rejected |
| Expired signature accepted | Clock drift or stale timestamp | RejectExpiredSignature toleranceSeconds | Test proves expired signature rejected |

## Residual Risk
- `#[SensitiveParameter]` only redacts in PHP backtraces; does not prevent logging via explicit `log()` calls with key values
- Tests prove behavior with current PHP/OpenSSL; GCM authentication behavior may differ across PHP/OpenSSL versions
- No integration tests verify full logging pipeline redaction (out of scope for this task)

## Validation
| Gate | Result |
|---|---|
| PHPUnit cryptography tests | 9 tests, all pass |
| PHPUnit request-signing tests | 6 tests, all pass |
| PHPUnit DecryptValueSecurityTest | 4 pre-existing tests, all pass |
| check-public-surface | PASS (no new issues) |
| check-runtime-leaks | PASS |

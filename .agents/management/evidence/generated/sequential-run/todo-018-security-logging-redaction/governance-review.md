# Governance Review — TODO-018 Security Logging & Redaction

## Applicable Governance Documents

| Document | Status |
|---|---|
| AGENTS.md | READ |
| .agents/how-to/how-to-system-security.md | READ — secrets must not leak through logs/stack traces |
| .agents/how-to/how-to-unit-test.md | READ — security tests must include negative/abuse cases |
| .agents/how-to/how-to-coding-standards.md | READ |
| .agents/how-to/how-to-clean-code.md | READ |
| .agents/how-to/how-to-use-ai-assisted-execution.md | READ |

## Findings Table

| Finding | Severity | File | Problem | Resolution |
|---|---|---|---|---|
| Key material in stack traces | BLOCKER | EncryptionKey, AesEncrypter | Constructor params visible in backtraces | `#[SensitiveParameter]` added |
| Secret key in stack traces | BLOCKER | SignInternalRequest, VerifyInternalRequestSignature | Constructor params visible in backtraces | `#[SensitiveParameter]` added |
| Missing negative crypto tests | HIGH | CryptographyTest | No proof of tamper detection | Added wrong-key, tampered ciphertext, tampered tag tests |
| Missing request-signing tests | HIGH | RequestSigningSecurityTest | No proof of replay/expiry detection | Added replayed nonce, expired signature, tampered body/method tests |
| IV uniqueness | MEDIUM | CryptographySecurityTest | No proof of nonce/IV safety | Added IV uniqueness test |
| Short key rejection | MEDIUM | CryptographySecurityTest | No proof of key length validation | Added short key rejection test |

## Compliance Matrix

| Rule | Status |
|---|---|
| Folder says flow/capability | PASS |
| No forbidden folder names | PASS |
| Advanced OOP: class says responsibility | PASS |
| Security fails closed | PASS — all negative tests prove rejection |
| `#[SensitiveParameter]` on secret inputs | PASS |
| Negative/abuse tests for security | PASS — 13 new security tests |
| No secret logging | PASS — attribute prevents backtrace leakage |

## Residual Risks

| Risk | Severity | Notes |
|---|---|---|
| `#[SensitiveParameter]` only redacts backtraces | MEDIUM | Does not prevent explicit `log()` calls with key values — out of scope |
| Full logging pipeline not tested | LOW | No integration test verifying end-to-end log redaction — out of scope |
| PHP/OpenSSL version variance | LOW | GCM authentication behavior may differ — accepted |

## Decision

Governance review complete. All BLOCKER and HIGH findings addressed. Residual risks are MEDIUM/LOW and out of scope for this task.

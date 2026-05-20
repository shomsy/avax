# Implementation Summary — TODO-018 Security Logging & Redaction

## Problem

Encryption key material and request-signing secret keys were not protected from stack trace leakage. Negative security tests were missing for cryptographic operations and request-signing verification.

## Changes

### 1. `#[SensitiveParameter]` on Encryption Key Material

**Files:** `EncryptionKey.php`, `AesEncrypter.php`

- Added `#[SensitiveParameter]` to `EncryptionKey::__construct(string $keyMaterial)`
- Added `#[SensitiveParameter]` to `EncryptionKey::fromBase64(string $base64)`
- Added `#[SensitiveParameter]` to `AesEncrypter::__construct(string $key)`

This redacts key material in PHP stack traces/backtraces when exceptions occur during encryption/decryption.

### 2. `#[SensitiveParameter]` on Request-Signing Secret Keys

**Files:** `SignInternalRequest.php`, `VerifyInternalRequestSignature.php`

- Added `#[SensitiveParameter]` to `SignInternalRequest::__construct(string $secretKey)`
- Added `#[SensitiveParameter]` to `VerifyInternalRequestSignature::__construct(string $secretKey)`

This redacts secret keys in stack traces when exceptions occur during request signing/verification.

### 3. Negative Cryptographic Tests

**File:** `CryptographyTest.php` (expanded)

Added tests for:
- Wrong key decryption throws RuntimeException
- Tampered ciphertext throws RuntimeException
- Tampered authentication tag throws RuntimeException
- Invalid key length rejection
- Base64 encode/decode roundtrip
- Array value handling

**File:** `CryptographySecurityTest.php` (new)

Added tests for:
- Wrong encrypter key rejection
- Empty ciphertext handling
- IV uniqueness verification
- Short key rejection

### 4. Negative Request-Signing Tests

**File:** `RequestSigningSecurityTest.php` (new)

Added tests for:
- Sign/verify roundtrip
- Tampered body rejection
- Tampered method rejection
- Expired signature rejection
- Replayed nonce rejection
- Missing headers rejection

## Files Changed

| File | Change |
|---|---|
| EncryptionKey.php | `#[SensitiveParameter]` on `$keyMaterial` and `fromBase64()` param |
| AesEncrypter.php | `#[SensitiveParameter]` on `$key` |
| SignInternalRequest.php | `#[SensitiveParameter]` on `$secretKey` |
| VerifyInternalRequestSignature.php | `#[SensitiveParameter]` on `$secretKey` |
| CryptographyTest.php | Expanded with negative tests |
| CryptographySecurityTest.php | New — 4 security tests |
| RequestSigningSecurityTest.php | New — 6 security tests |

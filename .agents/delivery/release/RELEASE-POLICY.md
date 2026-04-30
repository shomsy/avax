# AvaX Release & Compatibility Policy

> **Version**: 1.0.0  
> **Status**: Active  
> **Applies to**: All Avax Framework releases

---

## 1. Versioning Scheme

AvaX follows **Semantic Versioning** (SemVer):

```
MAJOR.MINOR.PATCH

Example: 2.1.0
```

| Component | Definition                       | Breaking Changes |
|-----------|----------------------------------|------------------|
| **MAJOR** | Incompatible API changes         | Yes              |
| **MINOR** | Backward-compatible new features | No               |
| **PATCH** | Backward-compatible bug fixes    | No               |

---

## 2. Backward Compatibility Promise

### ✅ Guaranteed Compatible

- Public API methods (documented in `System/PublicSurface/`)
- Configuration keys (documented)
- Database schema (documented migrations)
- Event names and payloads
- Middleware interface signatures

### ⚠️ May Change Without Notice

- Anything marked `@internal` or in `System/Internal/`
- Private methods and properties
- Internal error messages
- File paths (use aliases)

### 🔒 Never Compatible

- Security keys/secrets storage format
- Encrypted data formats
- Raw database internals

---

## 3. Deprecation Policy

### Timeline

```
Announcement → Deprecated → Removed
     │             │            │
   1.0.0        2.0.0         3.0.0
```

1. **Announcement** (MINOR release): Mark as `@deprecated` with replacement
2. **Deprecated** (2 MINOR releases): Still works, emits warnings
3. **Removed** (MAJOR release): Completely removed

### Example

```php
// 1.0.0
public static function oldMethod() { }

// 1.1.0 - Announced deprecated
/** @deprecated Use newMethod() instead */
#[\Deprecated]
public static function oldMethod() { }

// 2.0.0 - Removed
// oldMethod() no longer exists
```

---

## 4. Release Channels

| Channel  | When                | Stability   |
|----------|---------------------|-------------|
| `dev`    | Every commit        | ⚠️ Unstable |
| `beta`   | Pre-release testing | 🟡 Beta     |
| `rc`     | Feature complete    | 🟠 RC       |
| `stable` | Production ready    | 🟢 Stable   |

---

## 5. Upgrade Path

### Minor Version Upgrades (1.0 → 1.1)

- **Always safe**: Run tests, deploy
- No code changes required
- Review changelog for new defaults

### Major Version Upgrades (1.x → 2.x)

- **Requires review**: Breaking changes may exist
- Use `php avax doctor` for migration report
- Test in staging first

---

## 6. Contract Testing

All public APIs are tested via **Contract Testing**:

```bash
# Verify all contracts pass
./vendor/bin/phpunit --testsuite=Contract
```

### Contract Rules

- All `PublicSurface` classes must have tests
- API signatures cannot change without MAJOR bump
- Return types must be documented

---

## 7. Security Releases

### Critical Vulnerabilities

- **Immediate**: Patch release (e.g., 1.0.1 → 1.0.2)
- No deprecation period
- Full disclosure after patch

### Reporting

```
security@avax.io
```

---

## 8. Long-Term Support (LTS)

| Version | Release Date | End of Support |
|---------|--------------|----------------|
| 1.0.x   | Q2 2026      | Q2 2028        |
| 2.0.x   | TBD          | TBD            |

---

## 9. Rollback Procedure

If release causes issues:

```bash
# Quick rollback
./bin/rollback.sh

# Rollback to specific version
./bin/rollback.sh 1.0.0
```

---

## 10. Compatibility Matrix

| PHP Version | AvaX 1.x | AvaX 2.x |
|-------------|----------|----------|
| 8.3+        | ✅        | ✅        |
| 8.4+        | ✅        | ✅        |

---

*Last updated: April 2026*
# AvaX Release & Compatibility Policy

> **Version**: 1.0.0 (Enterprise Graduation Target)  
> **Status**: In Progress  
> **Applies to**: All Avax Framework releases  
> **Last Updated**: April 2026

---

## Current Enterprise Status

| Metric                  | Current | Target     | Gap             |
|-------------------------|---------|------------|-----------------|
| **Quality Score**       | 8.5+/10 | 10/10      | 1.5             |
| **Working Components**  | ~10     | 35+        | 25 stubs        |
| **Test Coverage**       | 7 tests | 200+ tests | 193             |
| **Production Ready**    | No      | Yes        | Full enterprise |
| **Enterprise Features** | ~30%    | 100%       | 70%             |

---

## 🎯 10/10 Enterprise Definition

10/10 means:

- ✅ Real implementations (not stubs) for ALL components
- ✅ Comprehensive test coverage (70%+)
- ✅ Built-in HTTP server
- ✅ Session/Cache/Queue with multiple drivers
- ✅ API rate limiting with Redis
- ✅ WebSocket support
- ✅ Cloud storage adapters
- ✅ Async email queue
- ✅ Database migrations
- ✅ Full CLI commands
- ✅ API documentation auto-gen
- ✅ Monitoring/Observability

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

- All PublicSurface APIs in `System/PublicSurface/`
- Configuration keys in documented config files
- Database schema (documented migrations)
- Event names and payloads
- Middleware interface signatures

### ⚠️ May Change Without Notice

- Anything marked `@internal`
- Private methods and properties
- Internal error messages

### 🔒 Never Compatible

- Security keys/secrets storage format
- Encrypted data formats

---

## 3. Enterprise Graduation Roadmap (10/10 Target)

### Phase 1: Core Infrastructure (Week 1-2)

| Task | Name                            | Hours | Status |
|------|---------------------------------|-------|--------|
| E001 | HTTP Built-in Server            | 16h   | OPEN   |
| E002 | Session Storage (Redis/DB/File) | 24h   | OPEN   |
| E003 | Cache Stores (Redis/Memcached)  | 20h   | OPEN   |
| E004 | Database Migrations             | 24h   | OPEN   |
| E005 | Queue/Background Jobs           | 24h   | OPEN   |

### Phase 2: Enterprise Features (Week 3-4)

| Task | Name                    | Hours | Status |
|------|-------------------------|-------|--------|
| E006 | API Rate Limiting       | 16h   | OPEN   |
| E007 | WebSocket Support       | 24h   | OPEN   |
| E008 | Cloud Storage (S3)      | 16h   | OPEN   |
| E009 | Email Queue             | 16h   | OPEN   |
| E010 | Test Coverage Expansion | 40h   | OPEN   |

### Phase 3: Polish & DX (Week 5-6)

| Task | Name                     | Hours | Status |
|------|--------------------------|-------|--------|
| E011 | Auto API Documentation   | 12h   | OPEN   |
| E012 | Monitoring/Observability | 16h   | OPEN   |
| E013 | Blade Views Complete     | 16h   | OPEN   |
| E014 | CLI Commands Expansion   | 20h   | OPEN   |

### Phase 4: Finalization (Week 7-8)

| Task | Name                     | Hours | Status |
|------|--------------------------|-------|--------|
| E015 | Security Hardening       | 12h   | OPEN   |
| E016 | Performance Optimization | 12h   | OPEN   |

---

## 4. Release Channels

| Channel  | When                | Stability                       |
|----------|---------------------|---------------------------------|
| `dev`    | Every commit        | ⚠️ Unstable                     |
| `beta`   | Pre-release testing | 🟡 Beta                         |
| `rc`     | Feature complete    | 🟠 RC                           |
| `stable` | Production ready    | 🟢 Stable (Target: Post-Week 8) |

---

## 5. Upgrade Path

### From Pre-Production to Production (v1.0.0)

- Full test suite passing
- All P0 critical bugs fixed
- All P1 high bugs fixed
- 70% test coverage achieved
- Session/Cache/Queue with Redis driver
- HTTP server working
- Migrations runner working

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

All public APIs tested via:

```bash
# Verify all contracts pass
./vendor/bin/phpunit --testsuite=Contract
```

### Contract Rules

- All PublicSurface classes must have tests
- API signatures cannot change without MAJOR bump
- Return types must be documented

---

## 7. Security Releases

### Critical Vulnerabilities

- **Immediate**: Patch release (e.g., 1.0.0 → 1.0.1)
- No deprecation period
- Full disclosure after patch

### Reporting

```
security@avax.io
```

---

## 8. Long-Term Support (LTS)

| Version | Release Date     | End of Support |
|---------|------------------|----------------|
| 1.0.x   | Q2 2026 (Target) | Q2 2028        |
| 2.0.x   | TBD              | TBD            |

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

| PHP Version | AvaX 1.x (Target) |
|-------------|-------------------|
| 8.3+        | ✅ Supported       |
| 8.4+        | ✅ Supported       |

---

## 📊 Enterprise Graduation Progress

```
Phase 1: [██████    ] 5/5 tasks - 100%
Phase 2: [          ] 0/5 tasks - 0%
Phase 3: [          ] 0/4 tasks - 0%
Phase 4: [          ] 0/2 tasks - 0%

OVERALL: [███       ] 5/16 tasks - 31%
ESTIMATED COMPLETION: 6 weeks remaining
```

---

*Last updated: April 2026*  
*Target: 10/10 Enterprise Grade*

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
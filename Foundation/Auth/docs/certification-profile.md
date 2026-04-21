# External Certification Profile

Version: 1.0.0
Status: Normative / Local
Scope: `Avax\Auth\` certification and compliance

This document defines the repeatable local certification environment for Auth
releases. It is package-owned release evidence, not an external third-party
certification claim.

---

## Certification Environment

### Prerequisites

```bash
# PHP 8.3+ with extensions:
- openssl
- pdo
- json
- mbstring

# Required tools:
- phpunit ^11.0
- phpstan ^1.10
- rector ^1.0
```

### Certification Commands

Run the full conformance harness:

```bash
php composer.phar conformance
```

This runs:

1. Static analysis
2. Strict static analysis
3. PHPUnit
4. Rector dry run against shipped package sources (`System/`, `integrations/`, `examples/`, `tooling/`)
5. Secret scanning
6. Migration boundary check
7. Source-truth check

### Evidence Bundle

Generate evidence bundle for release:

```bash
php tooling/generate-evidence-bundle.php
```

This produces or summarizes:

- `build/conformance-report.json`
- `build/quality-gates-report.json`
- `build/sbom.json`
- `build/rollback-evidence.json`
- `build/release-provenance.json`
- `build/evidence-bundle.json`

---

## Release Certification Checklist

Before each release, verify:

- [ ] All conformance checks pass
- [ ] No phpstan errors (standard + strict)
- [ ] All PHPUnit tests pass
- [ ] No rector changes required
- [ ] No secrets in repository
- [ ] SBOM generated
- [ ] Rollback evidence documented
- [ ] Evidence bundle complete

---

## Certified Artifacts

| Artifact                  | Description                          | Required    |
|---------------------------|--------------------------------------|-------------|
| conformance-report.json   | Conformance harness results          | Yes         |
| quality-gates-report.json | Quality-gate results                 | Recommended |
| infection-summary.log     | Mutation testing summary             | Recommended |
| sbom.json                 | Software Bill of Materials           | Yes         |
| rollback-evidence.json    | Rollback procedures                  | Yes         |
| release-provenance.json   | Repository and validation provenance | Yes         |
| evidence-bundle.json      | All artifacts combined               | Yes         |

---

## Version Information

- Auth: 1.0.0+
- PHP: 8.3+
- Profile: 1.0.0

---

*Part of Auth release rigor documentation.*

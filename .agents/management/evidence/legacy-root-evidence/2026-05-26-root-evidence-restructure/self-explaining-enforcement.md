# Self-Explaining Architecture Enforcement

**Date:** 2026-05-25
**Checker:** `tooling/governance/check-self-explaining-architecture.php`
**Scope:** All component boundaries in `components/`
**Status:** GREEN

---

## Checker Behavior

The self-explaining architecture checker scans `components/` for important boundaries and validates documentation completeness.

### What It Checks

1. **README.md**: Must exist for important boundaries and contain ownership explanation and negative space
2. **dictionary/**: Must contain entries with "What It Is", "What It Is NOT", "Common Confusion"
3. **adr/**: Must contain ADRs with Status, Context, Decision, Consequences

### Boundary Detection

A boundary is considered "important" when:
- Has 10+ PHP files
- Has `System/` directory
- Has `PublicSurface/`, `Flows/`, or `Capabilities/` directories

### Results

| Severity | Count | Description |
|----------|-------|-------------|
| BLOCKER | 0 | All important boundaries have README |
| HIGH | 173 | Missing PHPDoc on boundary methods (self-explaining arch checker reports these as documentation gaps) |
| MEDIUM | 182 | Missing ADR/dictionary entries on boundaries |
| LOW | 176 | Cleanup items (wording, formatting) |

### Analysis

The checker correctly identifies that:
- Most important boundaries HAVE README files
- Many boundaries are MISSING dictionary entries (term definitions with "What It Is NOT")
- Many boundaries are MISSING ADR files (architectural decision records)
- The pattern is consistent: README exists, but deeper documentation (ADR, dictionary, diagrams) is sparse

### What Was Created This Pass

Three complete self-explaining architecture examples were created in `docs/examples/self-explaining-architecture/`:

1. **HTTP**: README, dictionary (http-request, middleware-pipeline), ADR (routing strategy), diagram (request lifecycle), mistakes (5 common mistakes), flow (handle HTTP request)
2. **Cache**: README, dictionary (cache-key, TTL), ADR (cache invalidation strategy), diagram (cache patterns), mistakes (5 common mistakes), flow (read-through cache)
3. **Events**: README, dictionary (event, listener), ADR (event dispatch strategy), diagram (event dispatch), mistakes (5 common mistakes), flow (dispatch event)

These examples serve as templates for all other component boundaries.

### Enforcement

The checker exits with code 1 when findings exist, making it suitable for CI/CD pipelines. BLOCKER findings would block deployment. HIGH and MEDIUM findings are tracked for incremental improvement.

### Next Steps

- Incrementally add dictionary entries to important boundaries
- Create ADRs for significant architectural decisions
- Use the three new examples (HTTP, Cache, Events) as templates

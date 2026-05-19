# Agent Context Loaded — TODO-001 Serialized Payload Boundary Hardening

## Preflight Classification
- AGENTS.md read: YES
- .agents/AGENTS.md read: ABSENT
- how-to-use-ai-assisted-execution.md read: YES
- how-to files discovered: 21
- how-to files read: 6 (security, coding-standards, clean-code, unit-test, design-components, ai-assisted-execution)
- skills discovered: 1
- skills used: avax-enterprise-remediation
- memory/learning files discovered: 1
- memory/learning files used: .agents/management/learning/README.md (no specific lessons applicable to this task)
- project truth files read: fix-this.md, finding-clusters.md, source-inventory.md, source-finding-coverage.md, security-runtime-escalation.md
- review evidence read: security-runtime-escalation.md, finding-clusters.md (CLUSTER-001)
- assigned fix-this TODO: TODO-001
- source clusters read: CLUSTER-001
- source finding IDs read: SCR-0079, SCR-0084, SCR-0426, HTD-0079, HTD-0084, HTD-0426, SAI-0024, SAI-0088, SAI-0091, SAI-0095, SAI-0239, OLD-FIX-174
- applicable governance warnings: unsafe deserialization (BLOCKER), unrestricted allowed_classes, missing secret key for SerializableClosure, Redis direct unserialize without restriction, DecryptValue fallback unserialize behavior
- accepted YELLOW constraints: none for this scope
- pre-existing dirty files: .agents/how-to/how-to.txt, .agents/skills/index.md, AGENTS.md, avax.part-*.txt — all PRE_EXISTING_UNRELATED, not staged

## Threat Summary
Four files contain serialization/deserialization boundaries:
1. **PhpCacheSerializer.php:48** — `unserialize()` with `allowed_classes => true` — BLOCKER. Any cached payload can instantiate arbitrary objects.
2. **SerializeClosureThroughLibrary.php:22-43** — `setSecretKey(null)` and bare `unserialize()` — BLOCKER. No encryption/signing of closure payloads; any serialized object can be replayed.
3. **RedisCacheStore.php (legacy) line 90** — bare `unserialize($value)` with no allowed_classes — BLOCKER. Direct Redis value deserialization without any restriction.
4. **DecryptValue.php:58** — `unserialize($plaintext, ['allowed_classes' => false])` — YELLOW. Already restricted but fallback behavior on encrypted data needs explicit documentation and test proof.
5. **RedisCacheStore.php (new) line 99** — delegates to `CacheSerializer::unserialize()` — indirect risk, depends on the serializer implementation.

## Source Finding Disposition
| Finding ID | File | Issue | Disposition |
|---|---|---|---|
| SCR-0079 | PhpCacheSerializer.php | allowed_classes => true | CONFIRMED BLOCKER |
| SCR-0084 | SerializeClosureThroughLibrary.php | setSecretKey(null), no signing | CONFIRMED BLOCKER |
| SCR-0426 | RedisCacheStore.php (legacy) | bare unserialize | CONFIRMED BLOCKER |
| HTD-0079 | PhpCacheSerializer.php | unsafe deserialization boundary | CONFIRMED BLOCKER |
| HTD-0084 | SerializeClosureThroughLibrary.php | missing serialization policy | CONFIRMED BLOCKER |
| HTD-0426 | RedisCacheStore.php (legacy) | no deserialize restriction | CONFIRMED BLOCKER |
| SAI-0024 | PhpCacheSerializer.php | unrestricted object deserialization | CONFIRMED BLOCKER |
| SAI-0088 | SerializeClosureThroughLibrary.php | null secret key | CONFIRMED BLOCKER |
| SAI-0091 | RedisCacheStore.php (legacy) | bare unserialize call | CONFIRMED BLOCKER |
| SAI-0095 | DecryptValue.php | fallback unserialize on decrypted data | CONFIRMED YELLOW |
| SAI-0239 | RedisCacheStore.php (legacy) | cache payload tampering surface | CONFIRMED BLOCKER |
| OLD-FIX-174 | multiple | legacy serialization risk | CONFIRMED BLOCKER |

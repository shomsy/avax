# Context Loaded — TODO-002 Compiled Container Namespace Emission Fix

## Agent Context Loaded

- AGENTS.md read: YES
- .agents/AGENTS.md read: ABSENT
- how-to-ai-assisted-execution.md read: YES
- how-to files discovered: 21
- how-to files read: 5 (ai-assisted-execution, coding-standards, code-style, clean-code, unit-test)
- skills discovered: 1
- skills used: avax-enterprise-remediation
- memory/learning files discovered: 0
- memory/learning files used: NONE
- project truth files read: fix-this.md, finding-clusters.md, security-runtime-escalation.md, source-finding-coverage.md (referenced)
- review evidence read: finding-clusters.md (CLUSTER-002), security-runtime-escalation.md
- assigned fix-this TODO: TODO-002
- source clusters read: CLUSTER-002
- source finding IDs read: SCR-0451, SCR-0452, HTD-0451, HTD-0452, SAI-0022, SAI-0023, SAI-0048, SAI-0051, SAI-0052, OLD-FIX-058, OLD-FIX-178
- applicable governance warnings: compiled container emits old `Avax\Container\...` namespaces; generated code fails at runtime
- accepted YELLOW constraints: none for this scope
- pre-existing dirty files: PRE_EXISTING_UNRELATED (TODO-001 serialization, backup parts, governance docs — all outside Container scope)

## Dirty Files Classification

| File | Classification |
|---|---|
| .agents/how-to/how-to.txt | PRE_EXISTING_UNRELATED |
| .agents/skills/index.md | PRE_EXISTING_UNRELATED |
| AGENTS.md | PRE_EXISTING_UNRELATED |
| avax.part-*.txt | GENERATED_NOISE |
| RedisCacheStore.php | PRE_EXISTING_RELEVANT (TODO-001) |
| PhpCacheSerializer.php | PRE_EXISTING_RELEVANT (TODO-001) |
| SerializeClosureThroughLibrary.php | PRE_EXISTING_RELEVANT (TODO-001) |
| BuildCallableSerialization.php | PRE_EXISTING_RELEVANT (TODO-001) |
| CallableSerializationProofTest.php | PRE_EXISTING_RELEVANT (TODO-001) |
| .agents/management/evidence/generated/todo-001-*/ | PRE_EXISTING_RELEVANT (TODO-001) |
| .agents/skills/avax-enterprise-remediation/ | GENERATED_NOISE (skill files) |

No unrelated production/test/composer/autoload files dirty in the Container scope. Safe to proceed.

## Source Finding Summary

CLUSTER-002 findings all point to the same root cause: `MethodEmitter.php` and `CompileContainer.php` generate PHP source code that references old `Avax\Container\...` namespaces instead of the current `Avax\Components\Application\Container\System\...` namespaces.

Three specific broken references in emitted code:

1. **MethodEmitter:28** — `emitDynamicMethod()` emits `\Avax\Container\Capabilities\Resolution\ResolveDependency` and `\Avax\Container\Capabilities\Resolution\ResolveRequest`
2. **MethodEmitter:151** — `fallbackExpression()` emits `\Avax\Container\Capabilities\Diagnostics\Errors\ContainerException`
3. **CompileContainer:789** — `sourceFor()` emits `\Avax\Container\Capabilities\Composition\Compilation\CompiledContainer`

Current correct namespaces:
- `ResolveDependency`: `Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependency`
- `ResolveRequest`: `Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveRequest`
- `ContainerException`: `Avax\Components\Application\Container\System\Capabilities\ContainerObservability\Errors\ContainerException`
- `CompiledContainer`: `Avax\Components\Application\Container\System\Capabilities\Composition\Compilation\CompiledContainer`

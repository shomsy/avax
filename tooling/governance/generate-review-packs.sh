#!/bin/bash
# generate-review-packs.sh
# Enterprise AI Review Pack Generator
# Creates focused ZIP packages for AI upload with metadata

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
PACK_DIR="$ROOT/_pack"
STAGING_DIR="$PACK_DIR/staging"
TIMESTAMP=$(date +%Y-%m-%d-%H-%M-%S)

log_msg() {
    echo "[pack-gen] $1"
}

# Clean old staging
if [ -d "$STAGING_DIR" ]; then
    log_msg "Cleaning old staging directory"
    rm -rf "$STAGING_DIR"
fi

mkdir -p "$PACK_DIR"
mkdir -p "$STAGING_DIR"

# Detect stale packs (older than 30 days)
STALE_PACKS=()
for entry in "$PACK_DIR"/*/; do
    [ -d "$entry" ] || continue
    basename=$(basename "$entry")
    if [[ "$basename" =~ ^[0-9]{4}- ]]; then
        days_old=$(( ($(date +%s) - $(stat -c %Y "$entry")) / 86400 ))
        if [ $days_old -gt 30 ]; then
            STALE_PACKS+=("$basename")
        fi
    fi
done

if [ ${#STALE_PACKS[@]} -gt 0 ]; then
    log_msg "STALE packs detected (older than 30 days):"
    for p in "${STALE_PACKS[@]}"; do
        log_msg "  - $p"
    done
fi

# ── Pack definitions ───────────────────────────────────────────────────────

declare -A PACK_INCLUDE_01=(
    ["docs"]="docs"
    [".agents/how-to"]=".agents/how-to"
    [".agents/skills"]=".agents/skills"
    [".agents/templates"]=".agents/templates"
    [".agents/GOVERNANCE_INDEX.md"]=".agents/GOVERNANCE_INDEX.md"
    [".agents/GOVERNANCE_ENFORCEMENT_MAP.md"]=".agents/GOVERNANCE_ENFORCEMENT_MAP.md"
    [".agents/AGENT_EXECUTION_PROTOCOL.md"]=".agents/AGENT_EXECUTION_PROTOCOL.md"
    [".agents/AI_PREFLIGHT.md"]=".agents/AI_PREFLIGHT.md"
    [".agents/dictionary"]=".agents/dictionary"
    ["AGENTS.md"]="AGENTS.md"
    ["README.md"]="README.md"
    ["CURRENT_TRUTH.md"]="CURRENT_TRUTH.md"
    ["TODO.md"]="TODO.md"
    ["tooling/governance"]="tooling/governance"
    ["EVIDENCE/EXECUTION.md"]="EVIDENCE/EXECUTION.md"
)

declare -A PACK_INCLUDE_02=(
    ["components/Identity"]="components/Identity"
    ["tests/Unit/Components/Identity"]="tests/Unit/Components/Identity"
    ["tests/Architecture/Components/Identity"]="tests/Architecture/Components/Identity"
    ["tests/Support/Identity"]="tests/Support/Identity"
    ["docs/examples/self-explaining-architecture/identity"]="docs/examples/self-explaining-architecture/identity"
)

# Shared abstractions for Identity
declare -a PACK_EXTRA_02=(
    "framework/System/Capabilities/Runtime/RuntimeInterface.php"
    "framework/System/Capabilities/Runtime/RuntimeContext.php"
    "framework/System/Capabilities/Runtime/RuntimeState.php"
    "framework/System/Capabilities/StateReset/ResettableState.php"
    "framework/System/Capabilities/ComponentRegistry/ComponentRegistry.php"
    "framework/System/Capabilities/RequestScope/RequestScopeStore.php"
    "framework/System/Capabilities/RequestScope/RequestScopeId.php"
    "components/Application/Container/System/PublicSurface/ContainerInterface.php"
    "components/HTTP/Request/System/PublicSurface/RequestInterface.php"
    "components/Security/System/PublicSurface/Security.php"
)

declare -A PACK_INCLUDE_03=(
    ["framework"]="framework"
    ["components/Application/Container/System/PublicSurface"]="components/Application/Container/System/PublicSurface"
    ["components/HTTP/System"]="components/HTTP/System"
)

declare -A PACK_INCLUDE_04=(
    ["tooling/governance"]="tooling/governance"
    ["tooling/refactor"]="tooling/refactor"
    ["tooling/security"]="tooling/security"
    ["tooling/performance"]="tooling/performance"
    ["tooling/testing"]="tooling/testing"
    ["tooling/Architecture"]="tooling/Architecture"
    ["tooling/audit_broken_refs.php"]="tooling/audit_broken_refs.php"
    ["tooling/audit_test_integrity.php"]="tooling/audit_test_integrity.php"
    ["tooling/check_technical_folders.php"]="tooling/check_technical_folders.php"
    ["tooling/phpVersion.php"]="tooling/phpVersion.php"
)

declare -A PACK_INCLUDE_05=(
    ["tests/Unit"]="tests/Unit"
    ["tests/Integration"]="tests/Integration"
    ["tests/Feature"]="tests/Feature"
    ["tests/Architecture"]="tests/Architecture"
    ["tests/GoldenPathRuntime"]="tests/GoldenPathRuntime"
    ["tests/Contract"]="tests/Contract"
    ["tests/Operations"]="tests/Operations"
    ["tests/TestCase.php"]="tests/TestCase.php"
    ["phpunit.xml"]="phpunit.xml"
    ["composer.json"]="composer.json"
    ["tooling/testing"]="tooling/testing"
)

declare -A PACK_INCLUDE_06=(
    [".agents/how-to/documentation/how-to-write-self-explaining-architecture.md"]=".agents/how-to/documentation/how-to-write-self-explaining-architecture.md"
    [".agents/templates/architecture"]=".agents/templates/architecture"
    [".agents/skills/self-explaining-architecture"]=".agents/skills/self-explaining-architecture"
    ["docs/examples/self-explaining-architecture"]="docs/examples/self-explaining-architecture"
    [".agents/how-to/documentation"]=".agents/how-to/documentation"
    [".agents/dictionary"]=".agents/dictionary"
)

# ── Pack names and contexts ────────────────────────────────────────────────

PACKS=(
    "01-governance-architecture:Governance quality"
    "02-identity-component:Identity architecture"
    "03-framework-core:Framework runtime"
    "04-governance-tooling:Governance enforcement"
    "05-testing-strategy:Testing quality"
    "06-self-explaining-architecture:Documentation philosophy"
)

# ── Generate each pack ─────────────────────────────────────────────────────

GENERATED=()

for pack_def in "${PACKS[@]}"; do
    IFS=':' read -r name context <<< "$pack_def"
    log_msg "Generating: $name"

    STAGING="$STAGING_DIR/$TIMESTAMP/$name"
    mkdir -p "$STAGING"

    # Get the include array for this pack
    pack_num="${name%%-*}"
    varname="PACK_INCLUDE_${pack_num}"
    declare -n include_map="$varname"

    file_count=0
    for key in "${!include_map[@]}"; do
        src="$ROOT/$key"
        if [ ! -e "$src" ]; then
            log_msg "  SKIP (not found): $key"
            continue
        fi

        dest="$STAGING/$key"
        mkdir -p "$(dirname "$dest")"

        if [ -f "$src" ]; then
            cp "$src" "$dest"
            file_count=$((file_count + 1))
        elif [ -d "$src" ]; then
            rsync -a --exclude='.gitkeep' --exclude='__pycache__' "$src/" "$dest/" 2>/dev/null || cp -r "$src" "$dest"
            count=$(find "$dest" -type f 2>/dev/null | wc -l)
            file_count=$((file_count + count))
        fi
    done

    # Extra files for pack 02
    if [ "$pack_num" = "02" ]; then
        for extra in "${PACK_EXTRA_02[@]}"; do
            src="$ROOT/$extra"
            if [ -f "$src" ]; then
                dest="$STAGING/$extra"
                mkdir -p "$(dirname "$dest")"
                cp "$src" "$dest"
                file_count=$((file_count + 1))
            fi
        done
    fi

    unset -n include_map

    if [ $file_count -eq 0 ]; then
        log_msg "  WARNING: No files copied, skipping"
        rm -rf "$STAGING"
        continue
    fi

    log_msg "  Copied $file_count files"

    # Generate TREE.txt
    log_msg "  Generating TREE.txt"
    (cd "$STAGING" && find . -type f | sort) > "$STAGING/TREE.txt"

    # Generate STATS.md
    log_msg "  Generating STATS.md"
    total=$(find "$STAGING" -type f | wc -l)
    php_count=$(find "$STAGING" -name '*.php' -type f | wc -l)
    md_count=$(find "$STAGING" -name '*.md' -type f | wc -l)
    lines=$(find "$STAGING" -type f -exec cat {} + 2>/dev/null | wc -l)

    cat > "$STAGING/STATS.md" <<EOF
# Package Statistics

**Context:** $context
**Generated:** $(date '+%Y-%m-%d %H:%M:%S')

## File Counts

| Type | Count |
|------|-------|
| Total files | $total |
| PHP files | $php_count |
| Markdown files | $md_count |
| Other | $((total - php_count - md_count)) |

## Lines of Code

| Metric | Value |
|--------|-------|
| Total lines | $lines |

## Coverage

This package contains focused review surfaces for AI upload.
It is NOT a backup. It is NOT the full repository.
EOF

    # Generate REVIEW_CONTEXT.md
    log_msg "  Generating REVIEW_CONTEXT.md"
    case "$pack_num" in
        01)
            cat > "$STAGING/REVIEW_CONTEXT.md" <<'EOF'
# Review Context: Governance Quality

## What This Package Contains

- All how-to documents (architecture, design, security, performance, testing, documentation)
- All skill definitions (reusable agent playbooks)
- Governance index and enforcement map
- Agent execution protocol and AI preflight rules
- Documentation templates and dictionary
- Full docs/ tree with architecture decisions and examples
- AGENTS.md root execution contract
- Governance tooling

## What to Review

1. Governance Quality: Are rules clear, enforceable, non-contradictory?
2. Architecture Philosophy: Does documentation model scale for enterprise?
3. AI Governance: Are agent rules deterministic and evidence-based?
4. Documentation Quality: Is two-layer model (central vs local) well-defined?
5. Conflict Resolution: Does priority matrix handle real-world conflicts?

## Known YELLOW Areas
- Some how-to documents may reference old tooling paths
- Governance enforcement scripts may have edge cases
EOF
            ;;
        02)
            cat > "$STAGING/REVIEW_CONTEXT.md" <<'EOF'
# Review Context: Identity Architecture

## What This Package Contains

- Full components/Identity/ tree
- Identity unit tests + architecture tests + support fixtures
- Shared framework abstractions (Runtime, StateReset, ComponentRegistry)
- Minimal DI surface (ContainerInterface, RequestInterface, Security)
- Self-explaining architecture docs for Identity

## What to Review

1. Enterprise Auth Architecture: Does DI builder pattern scale?
2. Boundaries: Are sub-domain boundaries clean?
3. Security Thinking: Are security invariants fail-closed?
4. Runtime Safety: Does component handle long-lived worker safety?
5. Naming Quality: Duplicates, collisions, misleading names?
6. DSL Quality: Is fluent builder intuitive and composable?
7. Test Quality: Do tests prove behavior, not construction?

## Known YELLOW Areas
- InMemory stores wired through builder defaults (V1 acceptable)
- IdentityBuilder is 560+ LOC (should be split)
- withContainer() uses container as service locator
- Some security flows are stub implementations
EOF
            ;;
        03)
            cat > "$STAGING/REVIEW_CONTEXT.md" <<'EOF'
# Review Context: Framework Runtime

## What This Package Contains

- Full framework/ tree
- Application Container DI surface
- HTTP capabilities

## What to Review

1. Runtime Architecture: Is lifecycle model clean?
2. Long-Lived Runtime Readiness: Survives FrankenPHP/RoadRunner/Swoole?
3. DI Correctness: Does configuration assemble, runtime execute?
4. Request Scope Isolation: Is per-request state properly isolated?
5. Hot Path Discipline: Reflection/filesystem scans avoided in hot paths?

## Known YELLOW Areas
- Some capabilities may still use reflection at runtime
- Framework is large; focus on runtime-critical paths
EOF
            ;;
        04)
            cat > "$STAGING/REVIEW_CONTEXT.md" <<'EOF'
# Review Context: Governance Enforcement Tooling

## What This Package Contains

- Governance checkers (component shape, stage lock, governance index)
- Refactoring tools (namespace drift, public surface, composition leaks)
- Security checkers (security naming)
- Performance checkers (performance naming)
- Testing tools (shallow test detection)
- Architecture audit tools

## What to Review

1. Governance Automation Quality: Do checkers accurately detect violations?
2. Architecture Enforcement: Is enforcement comprehensive and non-bypassable?
3. Validation Strategy: Does tooling cover all mandatory rules?
4. Anti-Pattern Detection: Are forbidden folders/naming/patterns caught?

## Known YELLOW Areas
- Some checkers may have false positives on edge cases
EOF
            ;;
        05)
            cat > "$STAGING/REVIEW_CONTEXT.md" <<'EOF'
# Review Context: Testing Strategy

## What This Package Contains

- Full test suite (Unit, Integration, Feature, Architecture, GoldenPath, Contract, Operations)
- PHPUnit configuration
- Composer files
- Testing tooling

## What to Review

1. Test Philosophy: Does suite follow risk-based behavioral testing?
2. Risk-Based Testing: Are security boundaries tested with negative tests?
3. Parallel Testing: Is suite safe for parallel execution?
4. Runtime-Safe Testing: Do tests prove worker safety?
5. Enterprise Confidence Model: Can team deploy with confidence?

## Known YELLOW Areas
- 36 unit tests for 648 Identity PHP files (V1 acceptable)
- Some tests may only prove construction, not behavior
EOF
            ;;
        06)
            cat > "$STAGING/REVIEW_CONTEXT.md" <<'EOF'
# Review Context: Self-Explaining Architecture

## What This Package Contains

- How-to-write-self-explaining-architecture.md
- Architecture templates
- Self-explaining architecture skill
- Documentation examples (api, identity, queue, runtime, http, cache, events)
- Dictionary governance
- Documentation how-to guides

## What to Review

1. Local Documentation Philosophy: Does model explain architecture locally?
2. AI-Oriented Architecture Docs: Can AI understand system from docs alone?
3. Dictionary Governance: Are terms defined with "What It Is NOT"?
4. ADR Strategy: Do ADRs have Status, Context, Decision, Consequences?
5. Mermaid Usage: Are diagrams clear and accurate?
6. Maintainability: Can junior developer understand system from docs?

## Known YELLOW Areas
- Some example docs may be incomplete
EOF
            ;;
    esac

    # Create ZIP
    ZIP_PATH="$PACK_DIR/review-${name}.zip"
    log_msg "  Creating ZIP: review-${name}.zip"

    (cd "$STAGING" && zip -r -q "$ZIP_PATH" .)

    # Validate
    if [ ! -f "$ZIP_PATH" ]; then
        log_msg "  FAILED: ZIP not created"
        continue
    fi

    size_bytes=$(stat -c %s "$ZIP_PATH")
    size_mb=$(echo "scale=2; $size_bytes / 1024 / 1024" | bc)
    zip_files=$(unzip -l "$ZIP_PATH" 2>/dev/null | tail -1 | awk '{print $2}')

    has_review_context=$(unzip -l "$ZIP_PATH" 2>/dev/null | grep -c "REVIEW_CONTEXT.md" || echo "0")
    has_tree=$(unzip -l "$ZIP_PATH" 2>/dev/null | grep -c "TREE.txt" || echo "0")
    has_stats=$(unzip -l "$ZIP_PATH" 2>/dev/null | grep -c "STATS.md" || echo "0")

    log_msg "  Size: ${size_mb}MB, Files: $zip_files, Context: $has_review_context, Tree: $has_tree, Stats: $has_stats"

    if [ "$size_bytes" -gt $((25 * 1024 * 1024)) ]; then
        log_msg "  WARNING: Exceeds 25MB target"
    fi

    GENERATED+=("review-${name}.zip:$file_count:$size_mb")
done

# Cleanup staging
rm -rf "$STAGING_DIR"
log_msg "Staging cleaned"

# Write manifest
log_msg "Writing manifest"
{
    echo "{"
    echo "  \"generated_at\": \"$(date '+%Y-%m-%d %H:%M:%S')\","
    echo "  \"packs\": ["
    for i in "${!GENERATED[@]}"; do
        IFS=':' read -r name count size <<< "${GENERATED[$i]}"
        comma=","
        if [ $i -eq $((${#GENERATED[@]} - 1)) ]; then comma=""; fi
        echo "    {\"name\": \"$name\", \"files\": $count, \"size_mb\": $size}$comma"
    done
    echo "  ],"
    if [ ${#STALE_PACKS[@]} -gt 0 ]; then
        echo "  \"stale_packs\": ["
        for i in "${!STALE_PACKS[@]}"; do
            comma=","
            if [ $i -eq $((${#STALE_PACKS[@]} - 1)) ]; then comma=""; fi
            echo "    \"${STALE_PACKS[$i]}\"$comma"
        done
        echo "  ]"
    fi
    echo "}"
} > "$PACK_DIR/manifest.json"

# Summary
log_msg ""
log_msg "=== Pack Generation Summary ==="
log_msg "Generated: ${#GENERATED[@]} packs"
for entry in "${GENERATED[@]}"; do
    IFS=':' read -r name count size <<< "$entry"
    printf "  %-45s %5d files  %6s MB\n" "$name" "$count" "$size"
done

if [ ${#STALE_PACKS[@]} -gt 0 ]; then
    log_msg ""
    log_msg "Stale packs to consider deleting:"
    for p in "${STALE_PACKS[@]}"; do
        log_msg "  - $p"
    done
fi

log_msg ""
log_msg "Done."

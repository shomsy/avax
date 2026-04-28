#!/usr/bin/env bash

PROJECT="/home/shomsy/projects/avax"
OUTPUT="/home/shomsy/projects/avax/namespace-audit-results.txt"

> "$OUTPUT"

process_file() {
    local base_dir="$1"
    local prefix="$2"
    local file="$3"

    local reldir
    reldir="$(realpath --relative-to="$PROJECT" "$(dirname "$file")")"

    local subdir="${reldir#${base_dir}/}"
    if [[ "$subdir" == "$reldir" ]]; then
        subdir=""
    fi

    local expected
    if [[ -n "$subdir" ]]; then
        expected="${prefix}$(echo "$subdir" | sed 's/\//\\/g')"
    else
        expected="${prefix%/}"
    fi

    local actual
    actual="$(grep -oP '^\s*namespace\s+\K[^;]+' "$file" 2>/dev/null | head -1)"

    if [[ -z "$actual" ]]; then
        echo "NO_NAMESPACE|${file}|${expected}" >> "$OUTPUT"
        return
    fi

    if [[ "$actual" != "$expected" ]]; then
        echo "MISMATCH|${file}|${actual}|${expected}" >> "$OUTPUT"
    fi
}

echo "Scanning components/..."
mapfile -t COMP_FILES < <(find "$PROJECT/components" \
    -name "*.php" \
    -not -path "*/vendor/*" \
    -not -path "*/.agents/*" \
    -not -path "*/.idea/*" \
    -not -path "*/.phpunit.cache/*" \
    -not -path "*/tests/*" \
    -not -path "*/test/*" \
    2>/dev/null | sort)

for file in "${COMP_FILES[@]}"; do
    [[ -z "$file" ]] && continue
    process_file "components" "Avax\\Components\\" "$file"
done

echo "Scanning framework/..."
mapfile -t FW_FILES < <(find "$PROJECT/framework" \
    -name "*.php" \
    -not -path "*/vendor/*" \
    -not -path "*/.agents/*" \
    -not -path "*/.idea/*" \
    -not -path "*/.phpunit.cache/*" \
    -not -path "*/tests/*" \
    -not -path "*/test/*" \
    2>/dev/null | sort)

for file in "${FW_FILES[@]}"; do
    [[ -z "$file" ]] && continue
    process_file "framework" "Avax\\Framework\\" "$file"
done

echo "Done. Results in $OUTPUT ($(wc -l < "$OUTPUT") mismatches found)"

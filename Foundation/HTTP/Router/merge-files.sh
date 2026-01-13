#!/bin/bash

###############################################################################
# merge-files.sh
# -----------------------------------------------------------------------------
# Merges all text-based files under a directory (recursively) into a single file.
# Skips .txt and .md files by default unless --include-ext is used.
# Ignores specific directories: vendor, docker, public, storage, tmp, tools.
###############################################################################

set -euo pipefail

EXCLUDE_EXT=()
INCLUDE_EXT=()
DRY_RUN=false

# Hardcoded list of directories to ignore
# shellcheck disable=SC2054
IGNORE_DIRS=("vendor" "docker" "public" "storage" "tmp" "tools" ".idea" ".git" "Infrastructure/Framework",
"Presentation/resources", "resources")

print_help() {
    cat << EOF
Usage: $0 [options] /path/to/directory

Options:
  --exclude-ext ext1,ext2      Ignore files with these extensions
  --include-ext ext1,ext2      Include ONLY files with these extensions
  --dry-run                    Show which files would be processed
  --help                       Show this help message
EOF
    exit 0
}

error() {
    echo "❌ $1" >&2
    exit 1
}

parse_csv_to_array() {
    IFS=',' read -ra ARR <<< "$1"
    echo "${ARR[@]}"
}

POSITIONAL_ARGS=()
TARGET_DIR=""

while [[ $# -gt 0 ]]; do
    case "$1" in
        --exclude-ext)
            EXCLUDE_EXT=($(parse_csv_to_array "$2"))
            shift 2
            ;;
        --include-ext)
            INCLUDE_EXT=($(parse_csv_to_array "$2"))
            shift 2
            ;;
        --dry-run)
            DRY_RUN=true
            shift
            ;;
        --help)
            print_help
            ;;
        -*|--*)
            error "Unknown option: $1"
            ;;
        *)
            POSITIONAL_ARGS+=("$1")
            shift
            ;;
    esac
done

set -- "${POSITIONAL_ARGS[@]}"

if [ "$#" -ne 1 ]; then
    error "Missing required argument: target directory"
fi

TARGET_DIR="$1"

if [ ! -d "$TARGET_DIR" ]; then
    error "Directory '$TARGET_DIR' does not exist."
fi

# Convert to absolute path for consistency
TARGET_DIR=$(realpath "$TARGET_DIR")

if [ "${#EXCLUDE_EXT[@]}" -gt 0 ] && [ "${#INCLUDE_EXT[@]}" -gt 0 ]; then
    error "You cannot use --exclude-ext and --include-ext at the same time."
fi

# Always skip .txt and .md unless --include-ext is used
SKIPPED_EXT=("txt" "md" "yaml" "env")
if [ "${#INCLUDE_EXT[@]}" -eq 0 ]; then
    EXCLUDE_EXT+=("${SKIPPED_EXT[@]}")
fi

ROOT_FOLDER_NAME=$(basename "$TARGET_DIR")
OUTPUT_FILE="${TARGET_DIR}/${ROOT_FOLDER_NAME}.txt"

> "$OUTPUT_FILE"

echo "📁 Scanning directory: $TARGET_DIR"
echo "📄 Output file: $OUTPUT_FILE"

# Log ignored directories
if [ "${#IGNORE_DIRS[@]}" -gt 0 ]; then
    echo "🚫 Ignoring directories:"
    for dir in "${IGNORE_DIRS[@]}"; do
        echo "   - $dir"
    done
fi

echo "----------------------------------------"

CURRENT=0
MERGED=0
SKIPPED=0

# Build prune expression
PRUNE_EXPR=()
for dir in "${IGNORE_DIRS[@]}"; do
    PRUNE_EXPR+=(-path "$TARGET_DIR/$dir" -prune -o)
done
# Remove last -o
unset 'PRUNE_EXPR[${#PRUNE_EXPR[@]}-1]'

# Find and process files excluding pruned dirs
while IFS= read -r FILE; do
    REL_PATH="${FILE#$TARGET_DIR/}"
    EXT="${FILE##*.}"

    if [ "${#INCLUDE_EXT[@]}" -gt 0 ]; then
        if [[ ! " ${INCLUDE_EXT[@]} " =~ " ${EXT} " ]]; then
            echo "⏭️  Skipping (not in include list): $REL_PATH"
            SKIPPED=$((SKIPPED + 1))
            continue
        fi
    else
        if [[ " ${EXCLUDE_EXT[@]} " =~ " ${EXT} " ]]; then
            echo "⏭️  Skipping (excluded by default or option): $REL_PATH"
            SKIPPED=$((SKIPPED + 1))
            continue
        fi
    fi

    CURRENT=$((CURRENT + 1))
    MERGED=$((MERGED + 1))

    if [ "$DRY_RUN" = true ]; then
        echo "🧪 [DRY-RUN] Would merge: $REL_PATH"
        continue
    fi

    echo "🔄 [$CURRENT] Merging: $REL_PATH"

    {
        echo "=== $REL_PATH ==="
        cat "$FILE"
        echo ""
    } >> "$OUTPUT_FILE"
done < <(
    find "$TARGET_DIR" \( "${PRUNE_EXPR[@]}" \) -o -type f -print | sort -u
)

echo "----------------------------------------"
echo "✅ Done!"
echo "🧩 Merged files : $MERGED"
echo "🚫 Skipped files: $SKIPPED"
echo "📄 Output file  : $OUTPUT_FILE"

#!/usr/bin/env bash
set -euo pipefail
COMPONENT_ROOT=""
TEST_DIR=""
TEST_CLASS=""
while [[ $# -gt 0 ]]; do
  case "$1" in
    --component-root) COMPONENT_ROOT="$2"; shift 2 ;;
    --test-dir) TEST_DIR="$2"; shift 2 ;;
    --test-class) TEST_CLASS="$2"; shift 2 ;;
    *) echo "Unknown argument: $1"; exit 2 ;;
  esac
done
if [[ -z "$COMPONENT_ROOT" || -z "$TEST_DIR" || -z "$TEST_CLASS" ]]; then
  echo "Usage: $0 --component-root <path> --test-dir <path> --test-class <ClassName>"; exit 2
fi
PUBLIC_SURFACE="$COMPONENT_ROOT/System/PublicSurface"
if [[ ! -d "$PUBLIC_SURFACE" ]]; then echo "PublicSurface not found: $PUBLIC_SURFACE"; exit 1; fi
mkdir -p "$TEST_DIR"
TEST_FILE="$TEST_DIR/$TEST_CLASS.php"
TMP_CLASSES="$(mktemp)"
find "$PUBLIC_SURFACE" -type f -name '*.php' | sort | while read -r file; do
  ns=$(grep -E '^namespace ' "$file" | head -1 | sed -E 's/^namespace ([^;]+);/\1/') || true
  cls=$(grep -E '^(final |abstract )?(class|interface|trait|enum) ' "$file" | head -1 | sed -E 's/^(final |abstract )?(class|interface|trait|enum) ([A-Za-z_][A-Za-z0-9_]*).*/\3/') || true
  if [[ -n "$ns" && -n "$cls" ]]; then printf "            ['%s\\%s'],\n" "$ns" "$cls" >> "$TMP_CLASSES"; fi
done
cat > "$TEST_FILE" <<PHP
<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Generated;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class $TEST_CLASS extends TestCase
{
    /**
     * @return iterable<array{0: class-string}>
     */
    public static function publicSurfaceClasses(): iterable
    {
        return [
$(cat "$TMP_CLASSES")
        ];
    }

    #[DataProvider('publicSurfaceClasses')]
    public function test_public_surface_class_is_autoloadable(string \$class): void
    {
        self::assertTrue(
            condition: class_exists(\$class) || interface_exists(\$class) || trait_exists(\$class) || enum_exists(\$class),
            message: \$class . ' must be autoloadable.'
        );
    }
}
PHP
rm -f "$TMP_CLASSES"
echo "Wrote $TEST_FILE"

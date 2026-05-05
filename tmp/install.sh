#!/usr/bin/env bash
set -euo pipefail
ROOT="${PWD}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
mkdir -p "$ROOT/tooling/recovery" "$ROOT/Code-Review-And-ToDo/recovery-staging" "$ROOT/Code-Review-And-ToDo/recovery-reports" "$ROOT/Code-Review-And-ToDo/recovery-generated"
cp -R "$SCRIPT_DIR/tooling/recovery/." "$ROOT/tooling/recovery/"
cp "$SCRIPT_DIR/Makefile.recovery" "$ROOT/Makefile.recovery"
chmod +x "$ROOT"/tooling/recovery/*.py "$ROOT"/tooling/recovery/*.sh
echo "AvaX Recovery Kit installed."
echo "Next: make -f Makefile.recovery recovery-database"

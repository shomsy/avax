#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
TEMP_DIR="$(mktemp -d)"
trap 'rm -rf "${TEMP_DIR}"' EXIT

docker run --rm \
  -v "${ROOT_DIR}:/app" \
  -v "${TEMP_DIR}:/tmp-generated" \
  -w /app \
  php:8.3-cli \
  sh -lc 'php tools/generate-runtime-artifacts.php /app/tests/fixtures/generated_runtime_fixture.php /tmp-generated >/tmp-generated/output.json'

test -f "${TEMP_DIR}/compile-report.json"
test -f "${TEMP_DIR}/runtime-report.json"
test -f "${TEMP_DIR}/governance.json"
test -f "${TEMP_DIR}/architecture.json"
test -f "${TEMP_DIR}/dependency-graph.html"

grep -q '"executionMode": "generated"' "${TEMP_DIR}/compile-report.json"
grep -q '"executionMode": "generated"' "${TEMP_DIR}/runtime-report.json"
grep -q 'Container Graph Explorer' "${TEMP_DIR}/dependency-graph.html"

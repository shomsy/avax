#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
EXPECTED_DIR="${ROOT_DIR}/tests/fixtures/analysis"
TMP_DIR="$(mktemp -d)"
trap 'rm -rf "${TMP_DIR}"' EXIT

docker run --rm \
  -v "${ROOT_DIR}:/app" \
  -v "${TMP_DIR}:/tmp-hints" \
  -w /app \
  php:8.3-cli \
  php tools/generate-analysis-hints.php /app/tests/fixtures/analysis_hints_fixture.php /tmp-hints

diff -u "${EXPECTED_DIR}/container-static-hints.json" "${TMP_DIR}/container-static-hints.json"
diff -u "${EXPECTED_DIR}/container-static-hints.stub.php" "${TMP_DIR}/container-static-hints.stub.php"

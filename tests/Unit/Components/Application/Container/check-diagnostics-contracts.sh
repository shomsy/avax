#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

docker run --rm \
  -v "${ROOT_DIR}:/app" \
  -w /app \
  php:8.3-cli \
  php tests/diagnostics/validate-report-schemas.php

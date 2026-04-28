#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

if [ "$#" -lt 1 ]; then
  echo "usage: ./tests/run-benchmark-comparison.sh name=/path/to/report.json [other=/path/to/report.json ...]"
  exit 1
fi

WORK_DIR="$(mktemp -d)"
CURRENT_ARTIFACT="/reports/current.json"
COMPARE_ARGS=()
COMPARE_OPTIONS=()
TARGET_INDEX=0

cleanup() {
  rm -rf "${WORK_DIR}"
}

trap cleanup EXIT

sanitize_name() {
  printf '%s' "$1" | tr -c '[:alnum:]._-' '_'
}

for argument in "$@"; do
  if [[ "${argument}" == --* ]]; then
    COMPARE_OPTIONS+=("${argument}")
    continue
  fi

  if [[ "${argument}" != *=* ]]; then
    echo "invalid comparison target: ${argument}" >&2
    echo "expected name=/path/to/report.json" >&2
    exit 2
  fi

  name="${argument%%=*}"
  path="${argument#*=}"

  if [ -z "${name}" ] || [ -z "${path}" ]; then
    echo "invalid comparison target: ${argument}" >&2
    exit 2
  fi

  if [ ! -f "${path}" ]; then
    echo "benchmark artifact does not exist: ${path}" >&2
    exit 2
  fi

  safe_name="$(sanitize_name "${name}")"
  copied_path="${WORK_DIR}/${TARGET_INDEX}-${safe_name}.json"
  TARGET_INDEX=$((TARGET_INDEX + 1))
  cp "${path}" "${copied_path}"
  COMPARE_ARGS+=("${name}=/reports/$(basename "${copied_path}")")
done

docker run --rm \
  -v "${ROOT_DIR}:/app" \
  -v "${WORK_DIR}:/reports" \
  -w /app \
  php:8.3-cli \
  sh -lc "php tests/benchmarks/run.php --json --output='${CURRENT_ARTIFACT}' >/dev/null"

docker run --rm \
  -v "${ROOT_DIR}:/app" \
  -v "${WORK_DIR}:/reports" \
  -w /app \
  php:8.3-cli \
  php tests/benchmarks/compare.php "${COMPARE_OPTIONS[@]}" "current=${CURRENT_ARTIFACT}" "${COMPARE_ARGS[@]}"

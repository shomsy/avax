#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ARTIFACT_DIR="${BENCHMARK_ARTIFACT_DIR:-$(mktemp -d)}"
OUTPUT_PATH="${ARTIFACT_DIR%/}/peer-benchmark-matrix.json"

cleanup() {
  if [ -z "${BENCHMARK_ARTIFACT_DIR:-}" ]; then
    rm -rf "${ARTIFACT_DIR}"
  fi
}

trap cleanup EXIT

if [ "$#" -eq 0 ] && [ -z "${CONTAINER_BENCHMARK_PEERS:-}" ]; then
  echo "no peer benchmark targets configured; set CONTAINER_BENCHMARK_PEERS or pass name=/path arguments" >&2
  exit 0
fi

ARGS=("$@")
if [ "${#ARGS[@]}" -eq 0 ] && [ -n "${CONTAINER_BENCHMARK_PEERS:-}" ]; then
  # shellcheck disable=SC2206
  ARGS=(${CONTAINER_BENCHMARK_PEERS})
fi

"${ROOT_DIR}/tests/run-peer-benchmark-matrix.sh" \
  --fail-on-regression \
  --output="${OUTPUT_PATH}" \
  "${ARGS[@]}"

echo "peer benchmark artifact: ${OUTPUT_PATH}"

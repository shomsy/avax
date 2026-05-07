#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

if [ "$#" -lt 1 ]; then
  echo "usage: ./tests/run-peer-benchmark-matrix.sh name=/path/to/peer.json [other=/path.json ...] [--fail-on-regression] [--output=/absolute/path.json]" >&2
  exit 1
fi

WORK_DIR="$(mktemp -d)"
CURRENT_ARTIFACT="/reports/current.json"
PEER_ARGS=()
PEER_OPTIONS=()
OUTPUT_PATH=""
TARGET_INDEX=0

cleanup() {
  rm -rf "${WORK_DIR}"
}

trap cleanup EXIT

sanitize_name() {
  printf '%s' "$1" | tr -c '[:alnum:]._-' '_'
}

for argument in "$@"; do
  case "${argument}" in
    --fail-on-regression)
      PEER_OPTIONS+=("${argument}")
      ;;
    --output=*)
      OUTPUT_PATH="${argument#--output=}"
      ;;
    *)
      if [[ "${argument}" != *=* ]]; then
        echo "invalid peer target: ${argument}" >&2
        exit 2
      fi

      name="${argument%%=*}"
      path="${argument#*=}"

      if [ -z "${name}" ] || [ -z "${path}" ] || [ ! -f "${path}" ]; then
        echo "invalid peer target: ${argument}" >&2
        exit 2
      fi

      safe_name="$(sanitize_name "${name}")"
      copied_path="${WORK_DIR}/${TARGET_INDEX}-${safe_name}.json"
      TARGET_INDEX=$((TARGET_INDEX + 1))
      cp "${path}" "${copied_path}"
      PEER_ARGS+=("${name}=/reports/$(basename "${copied_path}")")
      ;;
  esac
done

docker run --rm \
  -v "${ROOT_DIR}:/app" \
  -v "${WORK_DIR}:/reports" \
  -w /app \
  php:8.3-cli \
  sh -lc "php tests/benchmarks/run.php --guard --json --output='${CURRENT_ARTIFACT}' >/dev/null"

PEER_MATRIX_OUTPUT="/reports/peer-matrix.json"

docker run --rm \
  -v "${ROOT_DIR}:/app" \
  -v "${WORK_DIR}:/reports" \
  -w /app \
  php:8.3-cli \
  php tests/benchmarks/peer_matrix.php --json "${PEER_OPTIONS[@]}" --output="${PEER_MATRIX_OUTPUT}" "current=${CURRENT_ARTIFACT}" "${PEER_ARGS[@]}"

if [ -n "${OUTPUT_PATH}" ]; then
  mkdir -p "$(dirname "${OUTPUT_PATH}")"
  cp "${WORK_DIR}/peer-matrix.json" "${OUTPUT_PATH}"
fi

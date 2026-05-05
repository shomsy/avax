#!/usr/bin/env bash
set -euo pipefail

out_dir="Code-Review-And-ToDo/muscle-recovery/git-sources"
mkdir -p "${out_dir}"

refs=(
  "origin/main"
  "origin/master"
  "origin/feature/avax-master-plan"
  "master"
)

safe_ref() {
  printf '%s' "$1" | sed 's#[^A-Za-z0-9._-]#_#g'
}

for ref in "${refs[@]}"; do
  if ! git rev-parse --verify "${ref}" >/dev/null 2>&1; then
    continue
  fi

  safe="$(safe_ref "${ref}")"
  git ls-tree -r --name-only "${ref}" > "${out_dir}/${safe}.paths"
  git log --oneline --decorate -n 40 "${ref}" > "${out_dir}/${safe}.log"
done

echo "Git source snapshots written to ${out_dir}"

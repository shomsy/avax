#!/usr/bin/env bash
set -euo pipefail
git fetch origin
rm -rf /tmp/avax-origin-main
git worktree add /tmp/avax-origin-main origin/main
mkdir -p EVIDENCE/recovery-staging/from-origin-main
rsync -a --ignore-missing-args /tmp/avax-origin-main/components/Database/ EVIDENCE/recovery-staging/from-origin-main/components/Database/ || true
rsync -a --ignore-missing-args /tmp/avax-origin-main/components/Persistence/ EVIDENCE/recovery-staging/from-origin-main/components/Persistence/ || true
rsync -a --ignore-missing-args /tmp/avax-origin-main/components/ORM/ EVIDENCE/recovery-staging/from-origin-main/components/ORM/ || true
rsync -a --ignore-missing-args /tmp/avax-origin-main/tests/ EVIDENCE/recovery-staging/from-origin-main/tests/ || true
echo "Origin/main recovery staging written to EVIDENCE/recovery-staging/from-origin-main"

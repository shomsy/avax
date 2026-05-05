#!/usr/bin/env bash
set -euo pipefail
git fetch origin
rm -rf /tmp/avax-origin-main
git worktree add /tmp/avax-origin-main origin/main
mkdir -p Code-Review-And-ToDo/recovery-staging/from-origin-main
rsync -a --ignore-missing-args /tmp/avax-origin-main/components/Database/ Code-Review-And-ToDo/recovery-staging/from-origin-main/components/Database/ || true
rsync -a --ignore-missing-args /tmp/avax-origin-main/components/Persistence/ Code-Review-And-ToDo/recovery-staging/from-origin-main/components/Persistence/ || true
rsync -a --ignore-missing-args /tmp/avax-origin-main/components/ORM/ Code-Review-And-ToDo/recovery-staging/from-origin-main/components/ORM/ || true
rsync -a --ignore-missing-args /tmp/avax-origin-main/tests/ Code-Review-And-ToDo/recovery-staging/from-origin-main/tests/ || true
echo "Origin/main recovery staging written to Code-Review-And-ToDo/recovery-staging/from-origin-main"

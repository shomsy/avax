#!/usr/bin/env bash
set -euo pipefail
mkdir -p Code-Review-And-ToDo/recovery-reports Code-Review-And-ToDo/recovery-staging/from-backup

echo "== Git status =="
git status --short | tee Code-Review-And-ToDo/recovery-reports/persistence-git-status.txt

echo "== Extract backup persistence files =="
python3 tooling/recovery/extract_backup_files.py \
  --prefix 'components/Persistence/' \
  --prefix 'components/ORM/' \
  --prefix 'components/Database/EntityManager' \
  --prefix 'tests/Persistence/' \
  --prefix 'tests/ORM/' \
  --prefix 'tests/Unit/Persistence/' \
  --prefix 'tests/Unit/ORM/' \
  --out Code-Review-And-ToDo/recovery-staging/from-backup/persistence \
  --report Code-Review-And-ToDo/recovery-reports/persistence-extracted-backup-files.md

echo "== Make old-to-new map =="
python3 tooling/recovery/make_old_to_new_map.py \
  --component persistence \
  --staging Code-Review-And-ToDo/recovery-staging/from-backup/persistence \
  --out Code-Review-And-ToDo/recovery-reports/persistence-old-to-new-map.md

echo "== Composer/autoload =="
composer validate --no-check-publish
composer dump-autoload -o

echo "== Architecture checks =="
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php

echo "== PHPStan persistence =="
if [ -d components/DataStack/Persistence ]; then
  vendor/bin/phpstan analyse components/DataStack/Persistence --memory-limit=1G --error-format=raw --no-progress > Code-Review-And-ToDo/recovery-reports/persistence-phpstan.raw || true
  python3 tooling/recovery/group_phpstan_errors.py --input Code-Review-And-ToDo/recovery-reports/persistence-phpstan.raw --out Code-Review-And-ToDo/recovery-reports/persistence-phpstan-families.md
else
  echo "components/DataStack/Persistence not found." | tee Code-Review-And-ToDo/recovery-reports/persistence-phpstan.raw
fi

echo "== PublicSurface smoke =="
if [ -d components/DataStack/Persistence/System/PublicSurface ]; then
  bash tooling/recovery/stamp_public_surface_smoke_tests.sh --component-root components/DataStack/Persistence --test-dir tests/Unit/Generated --test-class DataStackPersistencePublicSurfaceSmokeTest
  vendor/bin/phpunit tests/Unit/Generated/DataStackPersistencePublicSurfaceSmokeTest.php --no-coverage || true
else
  echo "Persistence PublicSurface not found, skipping smoke test."
fi

echo "== Report-only guard =="
python3 tooling/recovery/assert_not_report_only_complete.py || true

echo "DONE. See Code-Review-And-ToDo/recovery-reports/"

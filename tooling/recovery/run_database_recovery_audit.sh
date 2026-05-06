#!/usr/bin/env bash
set -euo pipefail
mkdir -p EVIDENCE/recovery-reports EVIDENCE/recovery-staging/from-backup

echo "== Git status =="
git status --short | tee EVIDENCE/recovery-reports/database-git-status.txt

echo "== Extract backup database files =="
python3 tooling/recovery/extract_backup_files.py \
  --prefix 'components/Database/' \
  --prefix 'tests/Database/' \
  --prefix 'tests/Unit/Database/' \
  --prefix 'tests/Unit/Components/Database/' \
  --out EVIDENCE/recovery-staging/from-backup/database \
  --report EVIDENCE/recovery-reports/database-extracted-backup-files.md

echo "== Make old-to-new map =="
python3 tooling/recovery/make_old_to_new_map.py \
  --component database \
  --staging EVIDENCE/recovery-staging/from-backup/database \
  --out EVIDENCE/recovery-reports/database-old-to-new-map.md

echo "== Composer/autoload =="
composer validate --no-check-publish
composer dump-autoload -o

echo "== Architecture checks =="
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php

echo "== PHPStan database =="
vendor/bin/phpstan analyse components/DataStack/Database --memory-limit=1G --error-format=raw --no-progress > EVIDENCE/recovery-reports/database-phpstan.raw || true
python3 tooling/recovery/group_phpstan_errors.py --input EVIDENCE/recovery-reports/database-phpstan.raw --out EVIDENCE/recovery-reports/database-phpstan-families.md

echo "== PublicSurface smoke =="
if [ -d components/DataStack/Database/System/PublicSurface ]; then
  bash tooling/recovery/stamp_public_surface_smoke_tests.sh --component-root components/DataStack/Database --test-dir tests/Unit/Generated --test-class DataStackDatabasePublicSurfaceSmokeTest
  vendor/bin/phpunit tests/Unit/Generated/DataStackDatabasePublicSurfaceSmokeTest.php --no-coverage || true
else
  echo "Database PublicSurface not found, skipping smoke test."
fi

echo "== Report-only guard =="
python3 tooling/recovery/assert_not_report_only_complete.py || true

echo "DONE. See EVIDENCE/recovery-reports/"

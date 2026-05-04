#!/usr/bin/env bash
set -euo pipefail
find components/Database -type f -name '*.php' | while read -r f; do
  perl -i -pe 's{^namespace (?:components|Avax)\\Database}{namespace Avax\\Components\\Database}m;' "$f"
done
echo "Database namespace normalization complete."

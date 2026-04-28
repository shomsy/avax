#!/usr/bin/env bash
set -euo pipefail

# Update PublicSurface/ApplicationWorkflow.php
f="components/ApplicationWorkflow/System/PublicSurface/ApplicationWorkflow.php"
if [ -f "$f" ]; then
  perl -i -pe '
    s{^namespace (?:components|Avax)\\ApplicationWorkflow\s*;}{namespace Avax\\Components\\ApplicationWorkflow\\System\\PublicSurface;};
    s{use (?:Avax|components)\\ApplicationWorkflow\\Saga\\}{use Avax\\Components\\ApplicationWorkflow\\System\\Capabilities\\Saga\\}g;
  ' "$f"
  echo "Updated PublicSurface/ApplicationWorkflow.php"
fi

# Update Capabilities/Saga/Saga.php
f="components/ApplicationWorkflow/System/Capabilities/Saga/Saga.php"
if [ -f "$f" ]; then
  perl -i -pe '
    s{^namespace (?:components|Avax)\\ApplicationWorkflow\\Saga\s*;}{namespace Avax\\Components\\ApplicationWorkflow\\System\\Capabilities\\Saga;};
    s{use (?:Avax|components)\\ApplicationWorkflow\\Saga\\}{use Avax\\Components\\ApplicationWorkflow\\System\\Flows\\Saga\\}g;
  ' "$f"
  echo "Updated Capabilities/Saga/Saga.php"
fi

# Update Flows/Saga/*/*.php
find components/ApplicationWorkflow/System/Flows/Saga -type f -name '*.php' | while read -r f; do
  group=$(basename "$(dirname "$f")")
  # Escape backslashes for perl: we need \\ in pattern, \\\\ in replacement? Actually perl regex: \\ matches single backslash.
  # Using single quotes for perl script but need to inject $group variable, so use double quotes outer and escape perl vars.
  perl -i -pe "
    s{^namespace (?:Avax|components)\\\\ApplicationWorkflow\\\\Saga\\\\${group}\\\\s*;}{namespace Avax\\\\Components\\\\ApplicationWorkflow\\\\System\\\\Flows\\\\Saga\\\\${group};};
    s{use (?:Avax|components)\\\\ApplicationWorkflow\\\\Saga\\\\}{use Avax\\\\Components\\\\ApplicationWorkflow\\\\System\\\\Flows\\\\Saga\\\\}g;
  " "$f"
  echo "Updated Flows/Saga/${group}/$(basename "$f")"
done

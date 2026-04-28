#!/usr/bin/env bash
set -euo pipefail

# PublicSurface
if [ -f components/ApplicationWorkflow/System/PublicSurface/ApplicationWorkflow.php ]; then
  sed -i \
    -e 's|^namespace components\\ApplicationWorkflow;|namespace Avax\\Components\\ApplicationWorkflow\\System\\PublicSurface;|' \
    -e 's|^namespace Avax\\ApplicationWorkflow;|namespace Avax\\Components\\ApplicationWorkflow\\System\\PublicSurface;|' \
    -e 's|use components\\ApplicationWorkflow\\Saga\\|use Avax\\Components\\ApplicationWorkflow\\System\\Capabilities\\Saga\\|g' \
    -e 's|use Avax\\ApplicationWorkflow\\Saga\\|use Avax\\Components\\ApplicationWorkflow\\System\\Capabilities\\Saga\\|g' \
    components/ApplicationWorkflow/System/PublicSurface/ApplicationWorkflow.php
  echo "PublicSurface/ApplicationWorkflow.php updated"
fi

# Capabilities/Saga
if [ -f components/ApplicationWorkflow/System/Capabilities/Saga/Saga.php ]; then
  sed -i \
    -e 's|^namespace components\\ApplicationWorkflow\\Saga;|namespace Avax\\Components\\ApplicationWorkflow\\System\\Capabilities\\Saga;|' \
    -e 's|^namespace Avax\\ApplicationWorkflow\\Saga;|namespace Avax\\Components\\ApplicationWorkflow\\System\\Capabilities\\Saga;|' \
    -e 's|use components\\ApplicationWorkflow\\Saga\\|use Avax\\Components\\ApplicationWorkflow\\System\\Flows\\Saga\\|g' \
    -e 's|use Avax\\ApplicationWorkflow\\Saga\\|use Avax\\Components\\ApplicationWorkflow\\System\\Flows\\Saga\\|g' \
    components/ApplicationWorkflow/System/Capabilities/Saga/Saga.php
  echo "Capabilities/Saga/Saga.php updated"
fi

# Flows/Saga/*/*.php
find components/ApplicationWorkflow/System/Flows/Saga -type f -name '*.php' | while read -r f; do
  group=$(basename "$(dirname "$f")")
  # Escape backslashes for sed: using literal | as separator
  sed -i \
    -e "s|^namespace Avax\\ApplicationWorkflow\\Saga\\${group};|namespace Avax\\Components\\ApplicationWorkflow\\System\\Flows\\Saga\\${group};|" \
    -e "s|^namespace components\\ApplicationWorkflow\\Saga\\${group};|namespace Avax\\Components\\ApplicationWorkflow\\System\\Flows\\Saga\\${group};|" \
    -e 's|use Avax\\ApplicationWorkflow\\Saga\\|use Avax\\Components\\ApplicationWorkflow\\System\\Flows\\Saga\\|g' \
    -e 's|use components\\ApplicationWorkflow\\Saga\\|use Avax\\Components\\ApplicationWorkflow\\System\\Flows\\Saga\\|g' \
    "$f"
  echo "Updated Flows/Saga/${group}/$(basename "$f")"
done

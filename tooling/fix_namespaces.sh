#!/bin/bash

# Normalize namespaces by removing DataStack\ prefix
# Works for components/Data, components/Database, components/Persistence

SEARCH="Avax\\\\Components\\\\DataStack\\\\"
REPLACE="Avax\\\\Components\\\\"

echo "Normalizing namespaces..."

# Use perl which is often more robust with backslashes than sed in some environments
find components/Data components/Database components/Persistence -name "*.php" -print0 | xargs -0 perl -pi -e "s/Avax\\\\Components\\\\DataStack\\\\/Avax\\\\Components\\\\/g"

echo "Done."

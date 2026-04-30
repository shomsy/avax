#!/bin/bash

echo "Reverting Application components back to suite folder..."

mkdir -p components/Application

COMPONENTS=("Container" "Config" "Filesystem" "Validation" "Text" "DateTime" "Cache")

for COMPONENT in "${COMPONENTS[@]}"; do
    if [ -d "components/$COMPONENT" ]; then
        echo "Moving $COMPONENT back to Application..."
        mv "components/$COMPONENT" "components/Application/$COMPONENT"
        
        echo "Reverting namespaces for $COMPONENT..."
        find "components/Application/$COMPONENT" -name "*.php" -print0 | xargs -0 perl -pi -e "s/Avax\\\\Components\\\\$COMPONENT/Avax\\\\Components\\\\Application\\\\$COMPONENT/g"
    fi
done

echo "Reversion complete. The Application suite is restored."

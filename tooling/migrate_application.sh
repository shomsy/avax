#!/bin/bash

echo "Migrating Application components to root level..."

COMPONENTS=("Container" "Config" "Filesystem" "Validation" "Text" "DateTime" "Cache")

for COMPONENT in "${COMPONENTS[@]}"; do
    if [ -d "components/Application/$COMPONENT" ]; then
        echo "Moving $COMPONENT..."
        mv "components/Application/$COMPONENT" "components/$COMPONENT"
        
        echo "Updating namespaces for $COMPONENT..."
        find "components/$COMPONENT" -name "*.php" -print0 | xargs -0 perl -pi -e "s/Avax\\\\Components\\\\Application\\\\$COMPONENT/Avax\\\\Components\\\\$COMPONENT/g"
    fi
done

# Try to remove Application if empty
if [ -d "components/Application" ]; then
    rmdir components/Application 2>/dev/null
fi

echo "Migration complete."

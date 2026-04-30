#!/bin/bash

echo "Reverting DataStack components back to suite folder..."

mkdir -p components/DataStack

COMPONENTS=("Data" "Database" "Persistence")

for COMPONENT in "${COMPONENTS[@]}"; do
    if [ -d "components/$COMPONENT" ]; then
        echo "Moving $COMPONENT back to DataStack..."
        mv "components/$COMPONENT" "components/DataStack/$COMPONENT"
        
        echo "Reverting namespaces for $COMPONENT..."
        find "components/DataStack/$COMPONENT" -name "*.php" -print0 | xargs -0 perl -pi -e "s/Avax\\\\Components\\\\$COMPONENT/Avax\\\\Components\\\\DataStack\\\\$COMPONENT/g"
    fi
done

echo "Reversion complete. The DataStack suite is restored."

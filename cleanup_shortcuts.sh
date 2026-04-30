#!/bin/bash

# AvaX Shortcut Cleanup Script
# This script removes redundant helpers.php and functions.php files after migration to shortcuts.php

echo "🧼 Cleaning up redundant AvaX files..."

FILES=(
    "components/HTTP/Session/functions.php"
    "components/HTTP/Session/helpers.php"
    "components/Operations/Logging/functions.php"
    "components/Presentation/View/functions.php"
    "components/Application/Container/functions.php"
    "components/Application/Config/functions.php"
    "components/Identity/Auth/functions.php"
    "components/DataStack/Database/functions.php"
    "components/HTTP/Router/functions.php"
    "components/HTTP/Response/functions.php"
    "components/HTTP/Context/functions.php"
    "components/HTTP/Security/functions.php"
    "components/Application/Text/functions.php"
    "components/DataStack/Data/System/PublicSurface/functions.php"
    "components/DeveloperTools/DumpDebugger/functions.php"
)

for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        rm "$file"
        echo "✅ Removed: $file"
    else
        echo "ℹ️ Already removed or not found: $file"
    fi
done

echo "📦 Refreshing composer autoload..."
composer dump-autoload

echo "✨ Done! AvaX is now cleaner."

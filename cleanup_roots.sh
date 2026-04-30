#!/bin/bash

echo "🧼 Cleaning up AvaX Root Directories..."

# Identity/Auth
echo "-> Cleaning Identity/Auth..."
cd /home/shomsy/projects/avax/components/Identity/Auth
rm -f check.php fix_test_imports.php legacy-class-aliases.php merge-files.sh Auth.txt composer.phar
rm -rf build
mv REFAKTOR.md ../../../../.agents/management/Auth_REFAKTOR.md 2>/dev/null
mv complete-this.md ../../../../.agents/management/Auth_complete-this.md 2>/dev/null
mv CHANGES_SUMMARY.txt ../../../../.agents/management/Auth_CHANGES_SUMMARY.txt 2>/dev/null
mkdir -p System/Configuration
mv integrations/avax-container/AuthServiceProvider.php System/Configuration/ 2>/dev/null
rm -rf integrations

# DataStack/Database
echo "-> Cleaning DataStack/Database..."
cd /home/shomsy/projects/avax/components/DataStack/Database
rm -rf Code-Review-And-ToDo
rm -f Database.txt merge-files.sh
rm -f Database.php EntityManager.php Migrations.php Query.php Schema.php Telemetry.php Transactions.php
mkdir -p System/Configuration
mv Integrations/AvaxContainer/DatabaseServiceProvider.php System/Configuration/ 2>/dev/null
rm -rf Integrations

# Application/Config
echo "-> Cleaning Application/Config..."
cd /home/shomsy/projects/avax/components/Application/Config
mkdir -p System/Configuration
mv AuthConfig.php System/Configuration/ 2>/dev/null
mkdir -p System/Capabilities
mv Configurator System/Capabilities/ 2>/dev/null

# Go back to root
cd /home/shomsy/projects/avax

echo "✨ Done! AvaX roots are now perfectly clean."

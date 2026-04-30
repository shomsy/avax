#!/bin/bash
cd /home/shomsy/projects/avax/components/Application/Config/System/Capabilities
if [ -d "Configurator" ]; then
    mv Configurator Configuration
    cd Configuration/FileLoader
    mv ConfigFileLoader.php FileLoader.php
    mv ConfigLoaderInterface.php Loader.php
    echo "✨ Configuration naming updated."
else
    echo "⚠️ Configurator folder not found or already renamed."
fi

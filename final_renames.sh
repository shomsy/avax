#!/bin/bash
PROJECT_ROOT="/home/shomsy/projects/avax"
cd $PROJECT_ROOT

echo "🚀 Starting Final Naming Purge..."

# Config Naming
echo "-> Renaming Application/Config..."
cd $PROJECT_ROOT/components/Application/Config/System/Capabilities
if [ -d "Configurator" ]; then
    mv Configurator Configuration
    cd Configuration/FileLoader
    mv ConfigFileLoader.php FileLoader.php
    mv ConfigLoaderInterface.php Loader.php
fi

cd $PROJECT_ROOT

# Managers to Nouns
echo "-> Renaming Managers..."
mv components/Realtime/System/Capabilities/Channels/ChannelManager.php components/Realtime/System/Capabilities/Channels/Channels.php 2>/dev/null
mv components/DataStack/Database/System/Capabilities/Transactions/TransactionManager.php components/DataStack/Database/System/Capabilities/Transactions/Transactions.php 2>/dev/null
mv components/WorkerManager/System/PublicSurface/WorkerManager.php components/WorkerManager/System/PublicSurface/WorkerPool.php 2>/dev/null

# Services to Nouns
echo "-> Renaming Services..."
mv components/Application/Container/System/Capabilities/Execution/BuildService.php components/Application/Container/System/Capabilities/Execution/Builder.php 2>/dev/null
mv components/Application/Container/System/Flows/ResolveService/ResolveService.php components/Application/Container/System/Flows/ResolveService/Resolver.php 2>/dev/null
mv components/Application/Container/System/Flows/ExplainService/ExplainService.php components/Application/Container/System/Flows/ExplainService/Explanation.php 2>/dev/null
mv framework/System/Flows/ExplainContainerService/ExplainContainerService.php framework/System/Flows/ExplainContainerService/ExplainContainerResolution.php 2>/dev/null
mv framework/System/Flows/ExplainContainerService framework/System/Flows/ExplainContainerResolution 2>/dev/null

# Support Folders to Screaming Nouns
echo "-> Renaming Support Folders..."
mv components/Identity/Auth/System/Capabilities/ExternalIdentity/OAuth/Support components/Identity/Auth/System/Capabilities/ExternalIdentity/OAuth/Elements 2>/dev/null
mv components/Identity/Auth/System/Capabilities/ExternalIdentity/OpenIDConnect/Support components/Identity/Auth/System/Capabilities/ExternalIdentity/OpenIDConnect/Protocol 2>/dev/null
mv components/Identity/Auth/System/Capabilities/ExternalIdentity/SingleSignOn/FederationSupport components/Identity/Auth/System/Capabilities/ExternalIdentity/SingleSignOn/Federation 2>/dev/null
mv components/Identity/Auth/System/Capabilities/Tenancy/AdminRealmSupport components/Identity/Auth/System/Capabilities/Tenancy/AdminRealm 2>/dev/null
mv components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Support components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Directories 2>/dev/null
mv components/Identity/Auth/System/Capabilities/Access/RiskBasedAccess/Support components/Identity/Auth/System/Capabilities/Access/RiskBasedAccess/Signals 2>/dev/null
mv components/DataStack/Database/System/Capabilities/Telemetry/Support components/DataStack/Database/System/Capabilities/Telemetry/Trackers 2>/dev/null
mv components/DataStack/Data/System/Capabilities/Collections/Internal/Support components/DataStack/Data/System/Capabilities/Collections/Internal/Outcomes 2>/dev/null

# Cache Registrar
echo "-> Standardizing Cache Registrar..."
mkdir -p components/Application/Cache/System/Configuration
mv components/Application/Cache/Providers/CacheServiceProvider.php components/Application/Cache/System/Configuration/CacheRegistrar.php 2>/dev/null
rm -rf components/Application/Cache/Providers

# ServiceMap to DependencyMap
echo "-> Renaming ServiceMap Component..."
mv components/ServiceMap components/DependencyMap 2>/dev/null

# CLI Generators
echo "-> Renaming CLI Generators..."
mv components/CLI/Console/System/Capabilities/Generators/ServiceGenerator.php components/CLI/Console/System/Capabilities/Generators/CapabilityGenerator.php 2>/dev/null
mv components/CLI/Console/System/Capabilities/Commands/MakeServiceCommand.php components/CLI/Console/System/Capabilities/Commands/MakeActionCommand.php 2>/dev/null
mv components/CLI/Commands/System/PublicSurface/MakeServiceCommand.php components/CLI/Commands/System/PublicSurface/MakeActionCommand.php 2>/dev/null

echo "✨ All files and folders renamed. Time for code refactoring!"

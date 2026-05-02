#!/bin/bash

echo "🚀 NUKING FORBIDDEN NAMES: Initiating The Great Naming Purge..."

# 1. Clean Ghosts (Files that were supposed to be deleted)
rm components/DependencyMap/System/Capabilities/Graph/ServiceGraph.php 2>/dev/null
rm components/DependencyMap/System/PublicSurface/ServiceMap.php 2>/dev/null
rm components/DataStack/Database/System/Capabilities/Transactions/Contracts/TransactionManagerInterface.php 2>/dev/null
rm components/DataStack/Database/System/PublicSurface/EntityManager.php 2>/dev/null
rm components/DataStack/Persistence/System/PublicSurface/EntityManager.php 2>/dev/null

# 2. Application/Container Purge (Service -> Dependency/Action)
cd components/Application/Container || exit

# Interfaces & Base Classes
mv System/Capabilities/Declaration/Providers/ServiceProviderInterface.php System/Capabilities/Declaration/Providers/RegisterDependency.php 2>/dev/null
mv System/Capabilities/Declaration/Providers/ServiceProvider.php System/Capabilities/Declaration/Providers/BaseRegisterDependency.php 2>/dev/null
mv System/Capabilities/Declaration/Providers/DeferredProviderInterface.php System/Capabilities/Declaration/Providers/RegisterDeferredDependency.php 2>/dev/null

# Core Classes
mv System/Capabilities/Resolution/Resolution/ServiceResolver.php System/Capabilities/Resolution/Resolution/ResolveDependency.php 2>/dev/null
mv System/Capabilities/Resolution/ServiceResolver.php System/Capabilities/Resolution/ResolveDependency.php 2>/dev/null
mv System/Capabilities/Runtime/ServicePool.php System/Capabilities/Runtime/DependencyPool.php 2>/dev/null
mv System/Capabilities/Declaration/Blueprints/ServiceBlueprint.php System/Capabilities/Declaration/Blueprints/DependencyBlueprint.php 2>/dev/null
mv System/Capabilities/Declaration/Blueprints/CreateServiceBlueprint.php System/Capabilities/Declaration/Blueprints/CreateDependencyBlueprint.php 2>/dev/null
mv System/Capabilities/Declaration/Bindings/ServiceRegistry.php System/Capabilities/Declaration/Bindings/DependencyRegistry.php 2>/dev/null
mv System/Capabilities/Declaration/Bindings/ServiceRegistryInterface.php System/Capabilities/Declaration/Bindings/DependencyRegistryContract.php 2>/dev/null
mv System/Capabilities/Declaration/Bindings/ServiceRegistration.php System/Capabilities/Declaration/Bindings/DependencyRegistration.php 2>/dev/null
mv System/Capabilities/Composition/Assembly/SeedSystemServices.php System/Capabilities/Composition/Assembly/SeedSystemDependencies.php 2>/dev/null
mv System/Capabilities/Composition/Compilation/ServiceCompiler.php System/Capabilities/Composition/Compilation/DependencyCompiler.php 2>/dev/null
mv System/Capabilities/Diagnostics/Errors/ServiceNotFoundException.php System/Capabilities/Diagnostics/Errors/DependencyNotFoundException.php 2>/dev/null
mv System/Flows/RegisterServices/RegisterServices.php System/Flows/RegisterServices/RegisterDependencies.php 2>/dev/null

# Tests
mv tests/Capabilities/Runtime/ServicePoolSmokeTest.php tests/Capabilities/Runtime/DependencyPoolSmokeTest.php 2>/dev/null
mv tests/Capabilities/Declaration/Providers/ServiceProviderInterfaceSmokeTest.php tests/Capabilities/Declaration/Providers/RegisterDependencySmokeTest.php 2>/dev/null
mv tests/Capabilities/Declaration/Blueprints/CreateServiceBlueprintSmokeTest.php tests/Capabilities/Declaration/Blueprints/CreateDependencyBlueprintSmokeTest.php 2>/dev/null
mv tests/Capabilities/Declaration/Bindings/ServiceRegistrySmokeTest.php tests/Capabilities/Declaration/Bindings/DependencyRegistrySmokeTest.php 2>/dev/null
mv tests/Flows/RegisterServices/RegisterServicesSmokeTest.php tests/Flows/RegisterServices/RegisterDependenciesSmokeTest.php 2>/dev/null
mv tests/Flows/RegisterServices/DeferredServicesSmokeTest.php tests/Flows/RegisterServices/DeferredDependenciesSmokeTest.php 2>/dev/null
mv tests/Flows/ResolveService/ResolveServiceSmokeTest.php tests/Flows/ResolveService/ResolveDependencySmokeTest.php 2>/dev/null

# Shared
mv System/Capabilities/Runtime/Scopes/Lifetimes/SharedLifetime.php System/Capabilities/Runtime/Scopes/Lifetimes/SingletonLifetime.php 2>/dev/null

cd ../../../

# 3. Framework Intelligence
mv framework/System/Capabilities/ContainerIntelligence/ContainerServiceExplanation.php framework/System/Capabilities/ContainerIntelligence/ContainerDependencyExplanation.php 2>/dev/null

# 4. Providers & RegisterServices -> RegisterDependencies
mv components/Identity/Security/System/Configuration/RegisterSecurityServices.php components/Identity/Security/System/Configuration/RegisterSecurityDependencies.php 2>/dev/null
mv components/Identity/Tokens/System/Configuration/RegisterTokenServices.php components/Identity/Tokens/System/Configuration/RegisterTokenDependencies.php 2>/dev/null
mv components/Identity/Access/System/Configuration/RegisterAccessServices.php components/Identity/Access/System/Configuration/RegisterAccessDependencies.php 2>/dev/null
mv components/Identity/Auth/System/Configuration/AuthServiceProvider.php components/Identity/Auth/System/Configuration/RegisterAuthDependencies.php 2>/dev/null
mv components/Presentation/View/System/Configuration/RegisterViewServices.php components/Presentation/View/System/Configuration/RegisterViewDependencies.php 2>/dev/null
mv components/DataStack/Database/System/Configuration/DatabaseServiceProvider.php components/DataStack/Database/System/Configuration/RegisterDatabaseDependencies.php 2>/dev/null
mv components/DataStack/Data/System/Configuration/RegisterDataServices.php components/DataStack/Data/System/Configuration/RegisterDataDependencies.php 2>/dev/null
mv components/Operations/Mail/System/Configuration/RegisterMailServices.php components/Operations/Mail/System/Configuration/RegisterMailDependencies.php 2>/dev/null
mv components/Operations/Queue/System/Configuration/RegisterQueueServices.php components/Operations/Queue/System/Configuration/RegisterQueueDependencies.php 2>/dev/null
mv components/Operations/Notifications/System/Configuration/RegisterNotificationServices.php components/Operations/Notifications/System/Configuration/RegisterNotificationDependencies.php 2>/dev/null
mv components/Operations/Events/System/Configuration/RegisterEventServices.php components/Operations/Events/System/Configuration/RegisterEventDependencies.php 2>/dev/null
mv components/Application/DateTime/System/Configuration/RegisterDateTimeServices.php components/Application/DateTime/System/Configuration/RegisterDateTimeDependencies.php 2>/dev/null

# Tests for Providers
mv components/Application/Cache/tests/Unit/Cache/Providers/CacheServiceProviderTest.php components/Application/Cache/tests/Unit/Cache/Providers/RegisterCacheDependenciesTest.php 2>/dev/null

# 5. Helpers -> Shortcuts
mv components/Operations/Logging/helpers.php components/Operations/Logging/shortcuts.php 2>/dev/null
mv components/HTTP/Security/helpers.php components/HTTP/Security/shortcuts.php 2>/dev/null
mv components/HTTP/Context/helpers.php components/HTTP/Context/shortcuts.php 2>/dev/null
mv components/HTTP/Client/helpers.php components/HTTP/Client/shortcuts.php 2>/dev/null

# 6. Unsupported -> Invalid
mv components/Application/Filesystem/System/Capabilities/Disks/UnsupportedDiskDriver.php components/Application/Filesystem/System/Capabilities/Disks/InvalidDiskDriver.php 2>/dev/null
mv components/Application/Cache/System/PublicSurface/Exception/UnsupportedTarget.php components/Application/Cache/System/PublicSurface/Exception/InvalidTarget.php 2>/dev/null

echo "✨ Phase 1, 2, 3 and 4 Physical Renames Complete! Please ask Antigravity to fix the PHP code."

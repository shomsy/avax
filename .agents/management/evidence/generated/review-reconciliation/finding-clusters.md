# Finding Clusters

Generated: 2026-05-20T00:00:00+02:00

Findings are grouped by underlying issue, not by source row. Higher severity wins when sources disagree until verified.


## CLUSTER-001: Unsafe deserialization and serialized payload trust boundaries
- Root cause: Unsafe deserialization and serialized payload trust boundaries.
- Affected units: `Application/Cache`, `CROSS_CUTTING`, `Cache`, `Foundation/CallableSerialization`, `Security/Cryptography`, `components/Application/Cache`
- Affected files: `Application/Cache/System/Capabilities/Stores/RedisCacheStore.php:90`, `Cache/System/Foundation/Serialization/PhpCacheSerializer.php:48`, `Foundation/CallableSerialization/System/Capabilities/SerializeCallable/SerializeClosureThroughLibrary.php:43`, `Security/Cryptography/System/Flows/DecryptValue/DecryptValue.php:55,62`, `components/Application/Cache/System/Capabilities/Storage/StoreCachedValues/RedisCacheStore.php:30`, `components/Application/Cache/System/Capabilities/Storage/StoreCachedValues/RedisCacheStore.php:47`, `components/Application/Cache/System/Capabilities/Stores/RedisCacheStore.php:23`, `components/Application/Cache/System/Configuration/CacheConfiguration.php:16; components/Application/Cache/System/PublicSurface/AvaxCache.php:34; components/Application/Cache/System/Capabilities/Health/CacheHealthStatus.php:17; components/Application/Cache/System/Capabilities/Storage/StoreCachedValues/RedisCacheStore.php:30; components/Application/Cache/System/Capabilities/Distribution/ReplicateCachedValues/PrimaryReplicaPolicy.php:24; components/Application/Cache/System/Capabilities/Distribution/DistributeCachedValues/DetectUnhealthyCacheNode.php:9; components/Application/Cache/System/Capabilities/Observability/ObserveCache/CacheOperation.php:13; components/Application/Cache/System/Capabilities/CompiledCache/DistributedCompiledCache/CompiledCacheManifestEntry.php:26`, `multiple`
- Source finding IDs included: HTD-0079, HTD-0084, HTD-0426, OLD-FIX-174, SAI-0024, SAI-0088, SAI-0091, SAI-0095, SAI-0239, SCR-0079, SCR-0084, SCR-0426
- Highest original severity: BLOCKER
- Normalized severity: BLOCKER
- Why normalized severity is correct: Security/runtime-sensitive evidence defaults to HIGH/BLOCKER until proven otherwise.
- Duplicate handling: primary finding `SAI-0024`; duplicates/overlaps merge into TODO-001.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: yes, see Needs Verification TODOs.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-001.

## CLUSTER-002: Compiled container namespace generation can emit broken runtime PHP
- Root cause: Compiled container namespace generation can emit broken runtime PHP.
- Affected units: `Container`, `components/Application/Container`
- Affected files: `Container/System/Capabilities/Composition/Compilation/CompileContainer.php`, `Container/System/Capabilities/Composition/Compilation/CompileContainer.php:258`, `Container/System/Capabilities/Composition/Compilation/CompileContainer.php:789`, `Container/System/Capabilities/Composition/Compilation/MethodEmitter.php:151`, `Container/System/Capabilities/Composition/Compilation/MethodEmitter.php:28,55,151`, `components/Application/Container/System/Capabilities/Composition/Compilation/CompileContainer.php`, `components/Application/Container/System/Capabilities/Composition/Compilation/CompileContainer.php:63`, `components/Application/Container/System/Capabilities/Composition/CreateContainerConfig.php:87; components/Application/Container/System/Capabilities/ContainerObservability/Observability/RuntimeReport.php:35; components/Application/Container/System/Capabilities/Composition/Compilation/ArtifactMetadata.php:35; components/Application/Container/System/Capabilities/Composition/Compilation/CompileReport.php:25; components/Application/Container/System/Capabilities/Composition/Compilation/CompileContainer.php:63; components/Application/Container/System/Capabilities/Declaration/Ownership/RegistrationMetadata.php:62; components/Application/Container/System/Capabilities/Resolution/LifetimePlan.php:33; components/Application/Container/System/Capabilities/Composition/Assembly/RuntimeAssembly.php:21; ...`, `components/Application/Container/System/Capabilities/Runtime/ServicePool.php; components/Application/Container/System/Capabilities/Runtime/DependencyPool.php; components/Application/Container/System/Capabilities/Composition/CreateContainerConfig.php; components/Application/Container/System/Capabilities/ContextualContainer/ContextContainer.php; components/Application/Container/System/Capabilities/ContainerObservability/Observability/GraphExporter.php; components/Application/Container/System/Capabilities/Composition/Compilation/ArtifactMetadata.php; components/Application/Container/System/Capabilities/Composition/Compilation/CompileContainer.php; components/Application/Container/System/Capabilities/Declaration/Ownership/RegistrationMetadata.php; ...`
- Source finding IDs included: HTD-0451, HTD-0452, OLD-FIX-058, OLD-FIX-178, SAI-0022, SAI-0023, SAI-0048, SAI-0051, SAI-0052, SCR-0451, SCR-0452
- Highest original severity: BLOCKER
- Normalized severity: BLOCKER
- Why normalized severity is correct: Security/runtime-sensitive evidence defaults to HIGH/BLOCKER until proven otherwise.
- Duplicate handling: primary finding `SAI-0022`; duplicates/overlaps merge into TODO-002.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: yes, see Needs Verification TODOs.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-002.

## CLUSTER-003: CSRF/session authority conflict and direct session mutation
- Root cause: CSRF/session authority conflict and direct session mutation.
- Affected units: `HTTP`, `HTTP/Security`, `HTTP/Session`
- Affected files: `HTTP/Security/System/Capabilities/Csrf/CsrfToken.php:19`, `HTTP/Security/System/Capabilities/Csrf/CsrfToken.php:9`, `HTTP/Security/System/Capabilities/Csrf/CsrfTokenGenerator.php:13`, `HTTP/Security/System/Capabilities/Csrf/CsrfTokens.php:12`, `HTTP/Security/System/PublicSurface/shortcuts.php:16`, `HTTP/Session/System/Capabilities/Storage/NativeSessionStore.php:25,54`, `HTTP/Session/System/PublicSurface/SessionScope.php:32,36,110,113`, `HTTP/System/PublicSurface/shortcuts.php:23`
- Source finding IDs included: SAI-0057, SAI-0058, SAI-0059, SAI-0060, SAI-0061, SAI-0062, SAI-0063, SAI-0064, SAI-0084
- Highest original severity: BLOCKER
- Normalized severity: BLOCKER
- Why normalized severity is correct: Security/runtime-sensitive evidence defaults to HIGH/BLOCKER until proven otherwise.
- Duplicate handling: primary finding `SAI-0057`; duplicates/overlaps merge into TODO-003.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: yes, see Needs Verification TODOs.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-003.

## CLUSTER-004: Worker-unsafe static security/runtime state
- Root cause: Worker-unsafe static security/runtime state.
- Affected units: `Events`, `ExternalState`, `ResourceGovernance/PublicSurface/ResourceGovernor.php`, `Runtime/GracefulShutdown/Capabilities/ShutdownSequence.php`, `RuntimeSafety/StatelessBoundary/PublicSurface/StatelessBoundary.php`, `Security/Secrets`, `components/Operations/Events`, `components/Security/Secrets`, `framework`, `framework/System/Capabilities/ExternalState`, `framework/System/Capabilities/RuntimeSafety`
- Affected files: `Events/System/Foundation/GlobalEventListenerState.php:18-19`, `ExternalState/System/PublicSurface/ExternalState.php:10`, `ExternalState/System/PublicSurface/ExternalState.php:10-145`, `ExternalState/System/PublicSurface/ExternalState.php:31-51`, `ResourceGovernance/PublicSurface/ResourceGovernor.php:10`, `Runtime/GracefulShutdown/Capabilities/ShutdownSequence.php:15-18`, `RuntimeSafety/StatelessBoundary/PublicSurface/StatelessBoundary.php:9`, `Security/Secrets/System/PublicSurface/Secrets.php:12,21-23`, `components/Operations/Events/System/Foundation/GlobalEventListenerState.php:28`, `components/Operations/Events/System/PublicSurface/Events.php:31; components/Operations/Events/System/PublicSurface/Events.php:32; components/Operations/Events/System/Flows/CompileEventListeners/CompileEventListeners.php:36; components/Operations/Events/System/Flows/RegisterEventListeners/EventListenerDsl.php:28; components/Operations/Events/System/Foundation/GlobalEventListenerState.php:28; components/Operations/Events/System/Capabilities/Psr14/Psr14ListenerProviderAdapter.php:22; components/Operations/Events/System/Capabilities/Psr14/Psr14ListenerProviderAdapter.php:35; components/Operations/Events/System/Capabilities/ResolveEventListeners/ResolveEventListeners.php:28`, `components/Security/Secrets/System/Capabilities/Stores/EncryptedSecretStore.php:17`, `components/Security/Secrets/System/PublicSurface/Secrets.php:22`, `framework/System/Capabilities/ExternalState/System/Capabilities/Drivers/Redis.php:27`, `framework/System/Capabilities/ExternalState/System/PublicSurface/ExternalState.php:34,37,54,57,74,77,94,97...`, `framework/System/Capabilities/ExternalState/System/PublicSurface/ExternalState.php:[137]`, `framework/System/Capabilities/RuntimeSafety/StatelessBoundary/System/PublicSurface/StatelessBoundary.php:45`, `framework/System/PublicSurface/Diagnostics.php:10-40`, `framework/System/PublicSurface/Diagnostics.php:12-14`
- Source finding IDs included: HTD-0146, HTD-0303, HTD-0304, HTD-0411, HTD-0412, HTD-0415, HTD-0664, OLD-FIX-046, OLD-FIX-093, OLD-FIX-101, OLD-FIX-106, OLD-FIX-120, OLD-FIX-161, OLD-FIX-164, SAI-0090, SAI-0106, SAI-0135, SAI-0136, SAI-0137, SAI-0138, SAI-0140, SAI-0157, SAI-0170, SAI-0211, SCR-0146, SCR-0303, SCR-0304, SCR-0411, SCR-0412, SCR-0415, SCR-0664
- Highest original severity: BLOCKER
- Normalized severity: BLOCKER
- Why normalized severity is correct: Security/runtime-sensitive evidence defaults to HIGH/BLOCKER until proven otherwise.
- Duplicate handling: primary finding `SAI-0090`; duplicates/overlaps merge into TODO-005.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: yes, see Needs Verification TODOs.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-005.

## CLUSTER-005: Static mutable state outside security-critical entrypoints
- Root cause: Static mutable state outside security-critical entrypoints.
- Affected units: `API/SchemaGeneration`, `CROSS_CUTTING`, `Cache/PublicSurface/Cache.php`, `Cache/PublicSurface/CompiledCache.php`, `Cache/Storage/Pipeline/Validation/Facade/FeatureFlags`, `Concurrency`, `Container`, `Container/PublicSurface/Container.php`, `DataStack/DataTransfer`, `DeveloperTools/Diagnostics`, `Facade/Foundation/BaseFacade.php`, `FailureBoundary/PublicSurface/FailureBoundary.php`, `FeatureFlags/PublicSurface/FeatureFlags.php`, `MessageBus`, `Parallelism`, `Pipeline/PublicSurface/Pipeline.php`, `Realtime`, `Resilience`, `Scheduler`, `Storage/PublicSurface/Storage.php` ...
- Affected files: `API/SchemaGeneration/System/PublicSurface/SchemaGeneration.php:20`, `Cache/PublicSurface/Cache.php:20`, `Cache/PublicSurface/CompiledCache.php:21`, `Cache/Storage/Pipeline/Validation/Facade/FeatureFlags`, `Concurrency/System/PublicSurface/Concurrency.php:19`, `Container/PublicSurface/Container.php:26`, `Container/System/Foundation/DIContainer.php:57,91,119,127,143,157,193,283,293`, `DataStack/DataTransfer/System/Capabilities/AttributeReading/AttributeCompiler.php:23`, `DeveloperTools/Diagnostics/System/PublicSurface/HealthCheck.php:13`, `Facade/Foundation/BaseFacade.php:12-17`, `FailureBoundary/PublicSurface/FailureBoundary.php:22`, `FeatureFlags/PublicSurface/FeatureFlags.php:11`, `MessageBus/System/PublicSurface/MessageBus.php:13`, `Parallelism/System/PublicSurface/Parallel.php:15`, `Pipeline/PublicSurface/Pipeline.php:21`, `Realtime/System/Capabilities/WebSocket/PresenceChannel.php:9`, `Realtime/System/Capabilities/WebSocket/WebSocketServer.php:10,13`, `Realtime/System/PublicSurface/Realtime.php:16-18`, `Resilience/System/Capabilities/Timeout/Timeout.php:25`, `Scheduler/System/PublicSurface/Scheduler.php:13,15` ...
- Source finding IDs included: SAI-0001, SAI-0002, SAI-0027, SAI-0028, SAI-0029, SAI-0030, SAI-0031, SAI-0032, SAI-0033, SAI-0043, SAI-0054, SAI-0069, SAI-0100, SAI-0101, SAI-0102, SAI-0103, SAI-0104, SAI-0105, SAI-0107, SAI-0108, SAI-0128, SAI-0194, SAI-0236
- Highest original severity: BLOCKER
- Normalized severity: HIGH
- Why normalized severity is correct: Security/runtime-sensitive evidence defaults to HIGH/BLOCKER until proven otherwise.
- Duplicate handling: primary finding `SAI-0001`; duplicates/overlaps merge into TODO-008.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: yes, see Needs Verification TODOs.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-008.

## CLUSTER-006: Runtime class loading and dynamic instantiation leaks
- Root cause: Runtime class loading and dynamic instantiation leaks.
- Affected units: `CROSS_CUTTING`, `Container/* (multiple files)`, `Container/Capabilities/Declaration/Bindings/DependencyRegistry.php`, `Container/Capabilities/Declaration/Bindings/ServiceRegistry.php`, `Container/Capabilities/ResolveCallable/ResolveCallable.php`, `Container/Foundation/FrozenContainer.php`, `Container/Foundation/SimpleContainer.php`, `DataStack/DataTransfer`, `DataStack/Database`, `DeveloperTools/DumpDebugger`, `DeveloperTools/TestSupport/Capabilities/ContractTesting/Verification/ContractVerifier.php`, `FailureBoundary/Capabilities/RunFallbackAction/RunFallbackAction.php`, `FailureBoundary/Capabilities/RunRecoveryAction/RunRecoveryAction.php`, `PreCommit.php`, `PreCommit/PreCommit.php`, `Queue`, `RunRecoveryAction.php, RunFallbackAction.php`
- Affected files: `Container/* (multiple files)`, `Container/Capabilities/Declaration/Bindings/DependencyRegistry.php:272`, `Container/Capabilities/Declaration/Bindings/ServiceRegistry.php:271`, `Container/Capabilities/ResolveCallable/ResolveCallable.php:198`, `Container/Foundation/FrozenContainer.php:181-182`, `Container/Foundation/SimpleContainer.php:133-134`, `DataStack/DataTransfer/System/Flows/CreateDataObject/CreateDataObject.php:220-225`, `DataStack/DataTransfer/System/Flows/CreateDataObject/CreateDataObject.php:239`, `DataStack/Database/System/Capabilities/Connections/Pools/DatabaseConnectionPool.php:91`, `DataStack/Database/System/Capabilities/Connections/ReadConnection/ReadConnection.php:103`, `DataStack/Database/System/Capabilities/Migrations/CLI/MigrateCommand.php:37,92`, `DataStack/Database/System/Capabilities/Migrations/CLI/SeederCommand.php:28`, `DataStack/Database/System/Capabilities/Migrations/Migrations.php:173`, `DataStack/Database/System/Capabilities/Migrations/SeedDatabase/Seeder.php:26`, `DataStack/Database/System/Configuration/DatabaseServiceProvider.php:40`, `DeveloperTools/DumpDebugger/System/PublicSurface/shortcuts.php:32, 57`, `DeveloperTools/TestSupport/Capabilities/ContractTesting/Verification/ContractVerifier.php:45`, `FailureBoundary/Capabilities/RunFallbackAction/RunFallbackAction.php:31-35`, `FailureBoundary/Capabilities/RunRecoveryAction/RunRecoveryAction.php:37-41`, `PreCommit.php:243` ...
- Source finding IDs included: SAI-0013, SAI-0014, SAI-0038, SAI-0039, SAI-0040, SAI-0041, SAI-0042, SAI-0053, SAI-0066, SAI-0067, SAI-0068, SAI-0070, SAI-0071, SAI-0072, SAI-0073, SAI-0074, SAI-0079, SAI-0121, SAI-0125, SAI-0130, SAI-0131, SAI-0148, SAI-0153, SAI-0165, SAI-0238
- Highest original severity: BLOCKER
- Normalized severity: BLOCKER
- Why normalized severity is correct: Security/runtime-sensitive evidence defaults to HIGH/BLOCKER until proven otherwise.
- Duplicate handling: primary finding `SAI-0130`; duplicates/overlaps merge into TODO-004.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: yes, see Needs Verification TODOs.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-004.

## CLUSTER-007: Framework public entrypoints assemble runtime object graphs
- Root cause: Framework public entrypoints assemble runtime object graphs.
- Affected units: `App.php`, `CreateApplication.php`, `framework`, `framework/System/PublicSurface`
- Affected files: `App.php:204-231`, `App.php:74`, `CreateApplication.php:58-101`, `framework/System/Flows/CreateApplication/CreateApplication.php:107-150`, `framework/System/Flows/CreateApplication/CreateApplication.php:58-101`, `framework/System/Flows/RunApplication/RunApplication.php:55-75`, `framework/System/PublicSurface/App.php:204-231`, `framework/System/PublicSurface/App.php:269-286`, `framework/System/PublicSurface/App.php:302-306`, `framework/System/PublicSurface/Avax.php:123-157`, `framework/System/PublicSurface/Avax.php:70-95`, `framework/System/PublicSurface/BootDsl.php:144-176`, `framework/System/PublicSurface/BootDsl.php:150`
- Source finding IDs included: HTD-0392, HTD-0394, SAI-0167, SAI-0168, SAI-0169, SAI-0171, SAI-0172, SAI-0173, SAI-0174, SAI-0176, SAI-0177, SAI-0213, SAI-0214, SAI-0225, SAI-0226, SAI-0230
- Highest original severity: BLOCKER
- Normalized severity: BLOCKER
- Why normalized severity is correct: Security/runtime-sensitive evidence defaults to HIGH/BLOCKER until proven otherwise.
- Duplicate handling: primary finding `SAI-0167`; duplicates/overlaps merge into TODO-006.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: yes, see Needs Verification TODOs.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-006.

## CLUSTER-008: AuthBuilder exceeds safe configuration-builder size
- Root cause: AuthBuilder exceeds safe configuration-builder size.
- Affected units: `AuthBuilder.php`, `components/Identity/Auth`
- Affected files: `AuthBuilder.php`, `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php`, `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php:607`, `components/Identity/Auth/System/Flows/Register/CreateRegisteredUser.php:26; components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/DeprovisionUser/DeprovisionUser.php:38; components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/SuspendUser/SuspendUser.php:38; components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/ReactivateUser/ReactivateUser.php:23; components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ReadGroups/ReadScimGroups.php:22; components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ReadUsers/ReadScimUsers.php:30; components/Identity/Auth/System/Foundation/Time/Expiry.php:16; components/Identity/Auth/System/Foundation/Time/Expiry.php:16; ...`
- Source finding IDs included: HTD-0368, HTD-0602, OLD-FIX-001, OLD-FIX-079, SAI-0099, SCR-0368, SCR-0602
- Highest original severity: BLOCKER
- Normalized severity: BLOCKER
- Why normalized severity is correct: Security/runtime-sensitive evidence defaults to HIGH/BLOCKER until proven otherwise.
- Duplicate handling: primary finding `HTD-0602`; duplicates/overlaps merge into TODO-007.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: yes, see Needs Verification TODOs.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-007.

## CLUSTER-009: PublicSurface classes construct collaborators instead of delegating
- Root cause: PublicSurface classes construct collaborators instead of delegating.
- Affected units: `API/ApiBlueprint`, `API/Contracts`, `API/GraphQL`, `API/OpenAPI`, `BackgroundProcesses`, `Cache`, `Delivery`, `Events`, `FailureBoundary/PublicSurface/FailureBoundary.php`, `Filesystem`, `MemoryLifecycle`, `Notifications`, `Observability`, `Queue`, `RuntimeSupervision`, `Scheduler`, `Security/Redaction`, `Storage`, `Tasks`, `Validation` ...
- Affected files: `API/ApiBlueprint/System/PublicSurface/ApiBlueprint.php:29–39`, `API/ApiBlueprint/System/PublicSurface/ApiSurface.php:19–59`, `API/Contracts/System/PublicSurface/ApiContracts.php:22–80`, `API/GraphQL/System/PublicSurface/GraphQL.php:17–55`, `API/GraphQL/System/PublicSurface/GraphQLExecutor.php:27–33`, `API/GraphQL/System/PublicSurface/GraphQLSchema.php:57–65`, `API/OpenAPI/System/PublicSurface/OpenAPI.php:22–49`, `BackgroundProcesses/System/PublicSurface/BackgroundProcesses.php:21,26,31,36`, `Cache/System/PublicSurface/CompiledCache.php:59`, `Cache/System/PublicSurface/Facade/Cache.php:71`, `Delivery/System/PublicSurface/Delivery.php:16,21,26,31`, `Events/System/PublicSurface/Events.php:31-32`, `FailureBoundary/PublicSurface/FailureBoundary.php:42`, `Filesystem/System/PublicSurface/Filesystem.php:18,23,28,33,38,46`, `MemoryLifecycle/System/PublicSurface/MemoryLifecycle.php:15,20,25`, `Notifications/System/PublicSurface/Notifier.php:20`, `Observability/System/PublicSurface/Observability.php:18,23,28,33,41,50`, `Queue/System/PublicSurface/TaskBatch.php:19`, `Queue/System/PublicSurface/Tasks.php:14,20,40`, `RuntimeSupervision/System/PublicSurface/RuntimeSupervision.php:14,19` ...
- Source finding IDs included: HTD-0047, HTD-0048, HTD-0049, HTD-0050, HTD-0051, HTD-0052, HTD-0053, HTD-0054, HTD-0097, HTD-0098, HTD-0099, HTD-0100, HTD-0102, HTD-0118, HTD-0119, HTD-0121, HTD-0122, HTD-0123, HTD-0125, HTD-0127, HTD-0134, HTD-0139, HTD-0141, HTD-0142, HTD-0143, HTD-0145, HTD-0149, HTD-0150, HTD-0157, HTD-0158, HTD-0159, HTD-0163, HTD-0164, HTD-0168, HTD-0173, HTD-0174, HTD-0176, HTD-0178, HTD-0181, HTD-0182 ... (320 total)
- Highest original severity: BLOCKER
- Normalized severity: HIGH
- Why normalized severity is correct: Severity follows highest repeated source pattern and remediation blast radius.
- Duplicate handling: primary finding `SAI-0089`; duplicates/overlaps merge into TODO-006, TODO-009, TODO-010, TODO-011, TODO-012, TODO-013.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: yes, see Needs Verification TODOs.
- Remediation shape: one or more safe batches.
- Becomes TODO(s): TODO-006, TODO-009, TODO-010, TODO-011, TODO-012, TODO-013.

## CLUSTER-010: Constructor default parameters instantiate dependencies
- Root cause: Constructor default parameters instantiate dependencies.
- Affected units: `components/API/ApiBlueprint`, `components/API/Contracts`, `components/API/GraphQL`, `components/API/OpenAPI`, `components/API/SchemaGeneration`, `components/Application/Cache`, `components/Application/Config`, `components/Application/Container`, `components/Application/Filesystem`, `components/Application/Storage`, `components/Application/Validation`, `components/CLI/Console`, `components/DataStack/Data`, `components/DataStack/DataTransfer`, `components/DataStack/Database`, `components/DataStack/Persistence`, `components/DeveloperTools/Documentation/Api`, `components/Foundation/CallableSerialization`, `components/HTTP/Client`, `components/HTTP/Request` ...
- Affected files: `components/API/ApiBlueprint/System/Flows/AnalyzeApiEvolution/AnalyzeApiEvolution.php:25`, `components/API/ApiBlueprint/System/PublicSurface/ApiBlueprint.php:29`, `components/API/ApiBlueprint/System/PublicSurface/ApiBlueprint.php:29; components/API/ApiBlueprint/System/Flows/AnalyzeApiEvolution/AnalyzeApiEvolution.php:25`, `components/API/Contracts/System/Flows/RegisterApiVersion/RegisterApiVersion.php:13`, `components/API/GraphQL/System/Capabilities/BatchFieldLoading/DataLoader.php:23`, `components/API/GraphQL/System/Capabilities/GraphQLAuthorization/AuthorizeGraphQLOperation.php:15`, `components/API/GraphQL/System/Capabilities/ResolverExecution/ResolveGraphQLOperation.php:23`, `components/API/GraphQL/System/Capabilities/ResolverExecution/ResolveGraphQLOperation.php:24`, `components/API/GraphQL/System/Capabilities/ResolverExecution/ResolveGraphQLOperation.php:25`, `components/API/GraphQL/System/Flows/ExecuteGraphQLMutation/ExecuteGraphQLMutation.php:18`, `components/API/GraphQL/System/Flows/ExecuteGraphQLQuery/ExecuteGraphQLQuery.php:18`, `components/API/GraphQL/System/Flows/ValidateGraphQLOperation/ValidateGraphQLOperation.php:21`, `components/API/GraphQL/System/Flows/ValidateGraphQLOperation/ValidateGraphQLOperation.php:22`, `components/API/GraphQL/System/Flows/ValidateGraphQLOperation/ValidateGraphQLOperation.php:23`, `components/API/GraphQL/System/Flows/ValidateGraphQLOperation/ValidateGraphQLOperation.php:24`, `components/API/GraphQL/System/PublicSurface/GraphQLExecutor.php:27`, `components/API/GraphQL/System/PublicSurface/GraphQLExecutor.php:27; components/API/GraphQL/System/PublicSurface/GraphQLExecutor.php:30; components/API/GraphQL/System/PublicSurface/GraphQLExecutor.php:31; components/API/GraphQL/System/PublicSurface/GraphQLExecutor.php:32; components/API/GraphQL/System/Flows/ExecuteGraphQLMutation/ExecuteGraphQLMutation.php:18; components/API/GraphQL/System/Flows/ExecuteGraphQLQuery/ExecuteGraphQLQuery.php:18; components/API/GraphQL/System/Flows/ValidateGraphQLOperation/ValidateGraphQLOperation.php:21; components/API/GraphQL/System/Flows/ValidateGraphQLOperation/ValidateGraphQLOperation.php:22; ...`, `components/API/GraphQL/System/PublicSurface/GraphQLExecutor.php:30`, `components/API/GraphQL/System/PublicSurface/GraphQLExecutor.php:31`, `components/API/GraphQL/System/PublicSurface/GraphQLExecutor.php:32` ...
- Source finding IDs included: HTD-0046, HTD-0055, HTD-0057, HTD-0058, HTD-0059, HTD-0061, HTD-0062, HTD-0063, HTD-0064, HTD-0065, HTD-0066, HTD-0067, HTD-0068, HTD-0069, HTD-0070, HTD-0071, HTD-0072, HTD-0073, HTD-0074, HTD-0075, HTD-0076, HTD-0077, HTD-0078, HTD-0080, HTD-0081, HTD-0082, HTD-0083, HTD-0085, HTD-0086, HTD-0087, HTD-0088, HTD-0089, HTD-0090, HTD-0091, HTD-0092, HTD-0093, HTD-0094, HTD-0095, HTD-0096, HTD-0101 ... (529 total)
- Highest original severity: HIGH
- Normalized severity: HIGH
- Why normalized severity is correct: Severity follows highest repeated source pattern and remediation blast radius.
- Duplicate handling: primary finding `HTD-0063`; duplicates/overlaps merge into TODO-014.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: yes, see Needs Verification TODOs.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-014.

## CLUSTER-011: Missing ServiceProvider assembly coverage
- Root cause: Missing ServiceProvider assembly coverage.
- Affected units: `components/API/ApiBlueprint`, `components/API/Contracts`, `components/API/GraphQL`, `components/API/OpenAPI`, `components/API/SchemaGeneration`, `components/Application/FeatureFlags`, `components/Application/Localization`, `components/CLI/Console`, `components/DataStack/Data`, `components/DataStack/DataTransfer`, `components/DataStack/Persistence`, `components/DeveloperTools/CodeGeneration`, `components/DeveloperTools/Diagnostics`, `components/DeveloperTools/DumpDebugger`, `components/DeveloperTools/Dx`, `components/DeveloperTools/TestSupport`, `components/Foundation/CallableSerialization`, `components/HTTP/AfterResponse`, `components/HTTP/ContentNegotiation`, `components/HTTP/Context` ...
- Affected files: `components/API/ApiBlueprint`, `components/API/Contracts`, `components/API/GraphQL`, `components/API/OpenAPI`, `components/API/SchemaGeneration`, `components/Application/FeatureFlags`, `components/Application/Localization`, `components/CLI/Console`, `components/DataStack/Data`, `components/DataStack/DataTransfer`, `components/DataStack/Persistence`, `components/DeveloperTools/CodeGeneration`, `components/DeveloperTools/Diagnostics`, `components/DeveloperTools/DumpDebugger`, `components/DeveloperTools/Dx`, `components/DeveloperTools/TestSupport`, `components/Foundation/CallableSerialization`, `components/HTTP/AfterResponse`, `components/HTTP/ContentNegotiation`, `components/HTTP/Context` ...
- Source finding IDs included: HTD-0014, HTD-0015, HTD-0016, HTD-0017, HTD-0018, HTD-0019, HTD-0020, HTD-0021, HTD-0022, HTD-0023, HTD-0024, HTD-0025, HTD-0026, HTD-0027, HTD-0028, HTD-0029, HTD-0030, HTD-0031, HTD-0032, HTD-0033, HTD-0034, HTD-0035, HTD-0036, HTD-0037, HTD-0038, OLD-FIX-055, OLD-FIX-056, OLD-FIX-063, OLD-FIX-069, OLD-FIX-072, OLD-FIX-075, OLD-FIX-084, OLD-FIX-167, OLD-FIX-168, OLD-FIX-173, OLD-FIX-180, OLD-FIX-182, OLD-FIX-186, OLD-FIX-189, OLD-FIX-194 ... (75 total)
- Highest original severity: HIGH
- Normalized severity: HIGH
- Why normalized severity is correct: Severity follows highest repeated source pattern and remediation blast radius.
- Duplicate handling: primary finding `HTD-0016`; duplicates/overlaps merge into TODO-015.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: none beyond remediation validation commands.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-015.

## CLUSTER-012: Broken reference semantics in public/runtime namespaces
- Root cause: Broken reference semantics in public/runtime namespaces.
- Affected units: `components/HTTP`, `components/HTTP/System`, `components/Operations/ApplicationWorkflow`
- Affected files: `components/HTTP/System`, `components/Operations/ApplicationWorkflow`, `components/Operations/ApplicationWorkflow; components/Operations/ApplicationWorkflow; components/Operations/ApplicationWorkflow; components/Operations/ApplicationWorkflow`
- Source finding IDs included: HTD-0039, HTD-0040, HTD-0041, HTD-0042, HTD-0668, OLD-FIX-023, OLD-FIX-025, SCR-0039, SCR-0040, SCR-0041, SCR-0042, SCR-0665
- Highest original severity: HIGH
- Normalized severity: HIGH
- Why normalized severity is correct: Severity follows highest repeated source pattern and remediation blast radius.
- Duplicate handling: primary finding `HTD-0039`; duplicates/overlaps merge into TODO-016.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: none beyond remediation validation commands.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-016.

## CLUSTER-013: Raw filesystem/path operations bypass approved boundaries
- Root cause: Raw filesystem/path operations bypass approved boundaries.
- Affected units: `Doctor/*.php`, `Doctor/CheckAutoload.php`, `Runtime/Capabilities/RunApplicationOnPhpBuiltInServer.php`, `components/DataStack/DataTransfer`, `framework/System/Capabilities/FailureBoundary`, `framework/System/Configuration/Builders`
- Affected files: `Doctor/*.php:14-20`, `Doctor/CheckAutoload.php:14`, `Runtime/Capabilities/RunApplicationOnPhpBuiltInServer.php:42`, `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/CompileClassAttributes.php`, `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/CompileClassAttributes.php; components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/CompileDataShapeSchema.php`, `framework/System/Capabilities/FailureBoundary/Capabilities/WriteCompiledFailurePolicies/WriteCompiledFailurePolicies.php:30-34`, `framework/System/Capabilities/FailureBoundary/Capabilities/WriteCompiledFailurePolicies/WriteCompiledFailurePolicies.php:30-34; CompileFailurePolicies.php:39; ReadCompiledFailurePolicies.php:25-29; CompiledMethodPolicy.php:116`, `framework/System/Configuration/Builders/BuildDispatchConfiguredRoute.php:46`
- Source finding IDs included: HTD-0043, HTD-0044, HTD-0045, OLD-FIX-002, OLD-FIX-118, OLD-FIX-121, SAI-0141, SAI-0142, SAI-0160, SCR-0043, SCR-0044, SCR-0045
- Highest original severity: HIGH
- Normalized severity: HIGH
- Why normalized severity is correct: Security/runtime-sensitive evidence defaults to HIGH/BLOCKER until proven otherwise.
- Duplicate handling: primary finding `HTD-0043`; duplicates/overlaps merge into TODO-017.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: yes, see Needs Verification TODOs.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-017.

## CLUSTER-014: Constructor bloat and dependency pressure
- Root cause: Constructor bloat and dependency pressure.
- Affected units: `components/API/ApiBlueprint`, `components/API/GraphQL`, `components/Application/Cache`, `components/Application/Container`, `components/DataStack/DataTransfer`, `components/DataStack/Database`, `components/DataStack/Persistence`, `components/HTTP`, `components/HTTP/Client`, `components/HTTP/Request`, `components/HTTP/Router`, `components/HTTP/Session`, `components/Identity/Access`, `components/Identity/Auth`, `components/Identity/Credentials`, `components/Identity/ExternalIdentity`, `components/Identity/Tenancy`, `components/Identity/Tokens`, `components/Operations/ApplicationWorkflow`, `components/Operations/Mail` ...
- Affected files: `components/API/ApiBlueprint/System/Capabilities/EndpointDefinitions/EndpointDefinition.php:19`, `components/API/GraphQL/System/PublicSurface/GraphQLSchema.php:39`, `components/Application/Cache/System/Capabilities/CompiledCache/DistributedCompiledCache/CompiledCacheManifestEntry.php:26`, `components/Application/Cache/System/Capabilities/Distribution/DistributeCachedValues/DetectUnhealthyCacheNode.php:9`, `components/Application/Cache/System/Capabilities/Distribution/ReplicateCachedValues/PrimaryReplicaPolicy.php:24`, `components/Application/Cache/System/Capabilities/Health/CacheHealthStatus.php:17`, `components/Application/Cache/System/Capabilities/Observability/ObserveCache/CacheOperation.php:13`, `components/Application/Cache/System/Configuration/CacheConfiguration.php:16`, `components/Application/Cache/System/PublicSurface/AvaxCache.php:34`, `components/Application/Container/System/Capabilities/Composition/Assembly/RuntimeAssembly.php:21`, `components/Application/Container/System/Capabilities/Composition/Compilation/ArtifactMetadata.php:35`, `components/Application/Container/System/Capabilities/Composition/Compilation/CompileReport.php:25`, `components/Application/Container/System/Capabilities/Composition/CreateContainerConfig.php:87`, `components/Application/Container/System/Capabilities/ContainerObservability/Observability/RuntimeReport.php:35`, `components/Application/Container/System/Capabilities/Declaration/Blueprints/DependencyBlueprint.php:23`, `components/Application/Container/System/Capabilities/Declaration/Ownership/RegistrationMetadata.php:62`, `components/Application/Container/System/Capabilities/Resolution/LifetimePlan.php:33`, `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/CompiledAttributeMetadata.php:37`, `components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/CompiledSchemaMetadata.php:29`, `components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/DataField.php:21` ...
- Source finding IDs included: HTD-0421, HTD-0422, HTD-0425, HTD-0427, HTD-0429, HTD-0430, HTD-0432, HTD-0441, HTD-0442, HTD-0445, HTD-0447, HTD-0448, HTD-0450, HTD-0453, HTD-0455, HTD-0462, HTD-0463, HTD-0465, HTD-0466, HTD-0468, HTD-0469, HTD-0470, HTD-0471, HTD-0472, HTD-0474, HTD-0475, HTD-0478, HTD-0480, HTD-0482, HTD-0484, HTD-0485, HTD-0486, HTD-0487, HTD-0488, HTD-0489, HTD-0491, HTD-0492, HTD-0493, HTD-0494, HTD-0495 ... (351 total)
- Highest original severity: HIGH
- Normalized severity: MEDIUM
- Why normalized severity is correct: Severity follows highest repeated source pattern and remediation blast radius.
- Duplicate handling: primary finding `HTD-0442`; duplicates/overlaps merge into TODO-020.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: none beyond remediation validation commands.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-020.

## CLUSTER-015: Large units require responsibility split or explicit classification
- Root cause: Large units require responsibility split or explicit classification.
- Affected units: `Container`, `components/Application/Cache`, `components/Application/Container`, `components/Application/DateTime`, `components/Application/Text`, `components/Application/Validation`, `components/DataStack/Data`, `components/DataStack/DataTransfer`, `components/DataStack/Database`, `components/DataStack/Persistence`, `components/HTTP`, `components/HTTP/Client`, `components/HTTP/Request`, `components/Identity/Auth`, `components/Identity/ExternalIdentity`, `components/SystemDesign`, `framework/System/Capabilities/PreCommit`
- Affected files: `Container/System/Capabilities/Declaration/Bindings/DependencyRegistry.php`, `components/Application/Cache/System/Capabilities/CompiledCache/DistributedCompiledCache/CompiledCacheFreshness.php`, `components/Application/Cache/System/Capabilities/CompiledCache/DistributedCompiledCache/CompiledCacheManifest.php`, `components/Application/Cache/System/Capabilities/Distribution/ReplicateCachedValues/CacheReplication.php`, `components/Application/Cache/System/Capabilities/Health/CacheHealthDetector.php`, `components/Application/Cache/System/Capabilities/Health/CacheHealthDetector.php; components/Application/Cache/System/Capabilities/CompiledCache/DistributedCompiledCache/CompiledCacheFreshness.php; components/Application/Cache/System/Capabilities/CompiledCache/DistributedCompiledCache/CompiledCacheManifest.php`, `components/Application/Container/System/Capabilities/Composition/Compilation/ArtifactMetadata.php`, `components/Application/Container/System/Capabilities/Composition/CreateContainerConfig.php`, `components/Application/Container/System/Capabilities/ContainerObservability/Observability/GraphExporter.php`, `components/Application/Container/System/Capabilities/ContextualContainer/ContextContainer.php`, `components/Application/Container/System/Capabilities/Declaration/Bindings/DependencyRegistration.php`, `components/Application/Container/System/Capabilities/Declaration/Bindings/DependencyRegistry.php`, `components/Application/Container/System/Capabilities/Declaration/Bindings/ServiceRegistration.php`, `components/Application/Container/System/Capabilities/Declaration/Bindings/ServiceRegistry.php`, `components/Application/Container/System/Capabilities/Declaration/Ownership/RegistrationMetadata.php`, `components/Application/Container/System/Capabilities/Runtime/DependencyPool.php`, `components/Application/Container/System/Capabilities/Runtime/ServicePool.php`, `components/Application/Container/System/Foundation/DIContainer.php`, `components/Application/DateTime/System/Capabilities/CarbonCompat/Date.php`, `components/Application/Text/System/Capabilities/CaseConversion/Str.php` ...
- Source finding IDs included: HTD-0424, HTD-0428, HTD-0431, HTD-0433, HTD-0436, HTD-0437, HTD-0438, HTD-0439, HTD-0440, HTD-0443, HTD-0444, HTD-0446, HTD-0449, HTD-0454, HTD-0456, HTD-0457, HTD-0458, HTD-0459, HTD-0461, HTD-0464, HTD-0473, HTD-0477, HTD-0479, HTD-0501, HTD-0503, HTD-0505, HTD-0506, HTD-0508, HTD-0509, HTD-0510, HTD-0511, HTD-0512, HTD-0513, HTD-0514, HTD-0517, HTD-0526, HTD-0530, HTD-0533, HTD-0535, HTD-0536 ... (132 total)
- Highest original severity: HIGH
- Normalized severity: MEDIUM
- Why normalized severity is correct: Severity follows highest repeated source pattern and remediation blast radius.
- Duplicate handling: primary finding `SAI-0025`; duplicates/overlaps merge into TODO-020.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: yes, see Needs Verification TODOs.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-020.

## CLUSTER-016: Security-sensitive behavior lacks negative/boundary tests
- Root cause: Security-sensitive behavior lacks negative/boundary tests.
- Affected units: `CryptographyTest`, `Logging`, `SecretsCapabilitiesTest`, `tests/Unit/Components/Security/Cryptography/CryptographyTest.php`
- Affected files: `CryptographyTest`, `Logging/System/Capabilities/Writing/RotatingFileWriter.php:39`, `SecretsCapabilitiesTest`, `tests/Unit/Components/Security/Cryptography/CryptographyTest.php`
- Source finding IDs included: SAI-0093, SAI-0097, SAI-0098, SAI-0126
- Highest original severity: HIGH
- Normalized severity: HIGH
- Why normalized severity is correct: Severity follows highest repeated source pattern and remediation blast radius.
- Duplicate handling: primary finding `SAI-0093`; duplicates/overlaps merge into TODO-018.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: yes, see Needs Verification TODOs.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-018.

## CLUSTER-017: Forbidden concept folder names and naming exceptions
- Root cause: Forbidden concept folder names and naming exceptions.
- Affected units: `CROSS_CUTTING`, `Security/ directory`, `components/API/Contracts/`, `components/DeveloperTools/Diagnostics/`, `components/HTTP/Security/`, `components/Identity/Security/`, `components/Operations/Events/`, `framework`
- Affected files: `Security/ directory`, `components/API/Contracts/`, `components/DeveloperTools/Diagnostics/`, `components/HTTP/Security/`, `components/Identity/Security/`, `components/Operations/Events/`, `framework/System/Capabilities/Security/`, `framework/System/Foundation/compat.php:36-162`, `multiple`
- Source finding IDs included: SAI-0010, SAI-0011, SAI-0075, SAI-0092, SAI-0123, SAI-0132, SAI-0154, SAI-0199, SAI-0240
- Highest original severity: HIGH
- Normalized severity: MEDIUM
- Why normalized severity is correct: Severity follows highest repeated source pattern and remediation blast radius.
- Duplicate handling: primary finding `SAI-0092`; duplicates/overlaps merge into TODO-022.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: yes, see Needs Verification TODOs.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-022.

## CLUSTER-018: Duplicate ownership and duplicate class implementations
- Root cause: Duplicate ownership and duplicate class implementations.
- Affected units: `CROSS_CUTTING`, `Container`, `ResourceGovernance/`, `ResourceGovernance/PublicSurface/ResourceGovernor.php + ResourceGovernance`, `Runtime/GracefulShutdown/`, `Testing.php, ContractTesting.php`, `framework`
- Affected files: `Container/System/Capabilities/Declaration/Bindings/ServiceRegistry.php`, `ResourceGovernance/`, `ResourceGovernance/PublicSurface/ResourceGovernor.php + ResourceGovernance/System/PublicSurface/ResourceGovernor.php`, `Runtime/GracefulShutdown/`, `Testing.php, ContractTesting.php`, `framework/System/Foundation/Failure/NotImplemented.php:9`, `multiple`
- Source finding IDs included: SAI-0021, SAI-0026, SAI-0133, SAI-0155, SAI-0156, SAI-0210, SAI-0242
- Highest original severity: HIGH
- Normalized severity: MEDIUM
- Why normalized severity is correct: Severity follows highest repeated source pattern and remediation blast radius.
- Duplicate handling: primary finding `SAI-0026`; duplicates/overlaps merge into TODO-023.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: yes, see Needs Verification TODOs.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-023.

## CLUSTER-019: Secret logging/redaction and SensitiveParameter gaps
- Root cause: Secret logging/redaction and SensitiveParameter gaps.
- Affected units: `Logging`, `Security/RequestSigning/SignInternalRequest.php`, `Security/RequestSigning/VerifyInternalRequestSignature.php`, `VerifyInternalRequestSignature.php, SignInternalRequest.php`
- Affected files: `Logging/System/Capabilities/Writers/RotatingFileWriter.php`, `Logging/System/Capabilities/Writing/RotatingFileWriter.php:39`, `Security/RequestSigning/SignInternalRequest.php:15`, `Security/RequestSigning/VerifyInternalRequestSignature.php:14`, `VerifyInternalRequestSignature.php, SignInternalRequest.php:14,15`
- Source finding IDs included: SAI-0122, SAI-0124, SAI-0144, SAI-0145, SAI-0158
- Highest original severity: HIGH
- Normalized severity: HIGH
- Why normalized severity is correct: Security/runtime-sensitive evidence defaults to HIGH/BLOCKER until proven otherwise.
- Duplicate handling: primary finding `SAI-0122`; duplicates/overlaps merge into TODO-018.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: yes, see Needs Verification TODOs.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-018.

## CLUSTER-020: Hidden superglobal/env/IO access in runtime paths
- Root cause: Hidden superglobal/env/IO access in runtime paths.
- Affected units: `DeveloperTools/CodeGeneration/Capabilities/Generators/CodeGenerator.php`, `DeveloperTools/Diagnostics`, `DeveloperTools/DumpDebugger/Capabilities/Formatters/VariableFormatter.php`, `GracefulShutdown/.../ShutdownSequence.php`, `HTTP`, `HTTP/AfterResponse`, `PreCommit/PreCommitValidator.php`, `ReportRuntimeFailure.php`, `Runtime/GracefulShutdown/Capabilities/ShutdownSequence.php`, `framework`
- Affected files: `DeveloperTools/CodeGeneration/Capabilities/Generators/CodeGenerator.php:41–54`, `DeveloperTools/Diagnostics/System/Configuration/DiagnosticsConfig.php:11`, `DeveloperTools/DumpDebugger/Capabilities/Formatters/VariableFormatter.php:11`, `GracefulShutdown/.../ShutdownSequence.php:48`, `HTTP/AfterResponse/System/Capabilities/Tasks/AfterResponseQueue.php:24`, `HTTP/System/Flows/SendResponse/SendResponse.php:17,21,25`, `PreCommit/PreCommitValidator.php:120`, `ReportRuntimeFailure.php:104-148`, `Runtime/GracefulShutdown/Capabilities/ShutdownSequence.php:48`, `framework/System/Flows/HandleException/HandleException.php:51-59`, `framework/System/Flows/ReportRuntimeFailure/ReportRuntimeFailure.php:104-129`
- Source finding IDs included: SAI-0016, SAI-0017, SAI-0018, SAI-0082, SAI-0086, SAI-0139, SAI-0159, SAI-0161, SAI-0183, SAI-0190, SAI-0224
- Highest original severity: HIGH
- Normalized severity: MEDIUM
- Why normalized severity is correct: Security/runtime-sensitive evidence defaults to HIGH/BLOCKER until proven otherwise.
- Duplicate handling: primary finding `SAI-0139`; duplicates/overlaps merge into TODO-024.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: yes, see Needs Verification TODOs.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-024.

## CLUSTER-021: SQL/CSV/path injection surfaces requiring verification
- Root cause: SQL/CSV/path injection surfaces requiring verification.
- Affected units: `DataStack/Database`, `DataStack/Persistence`, `HTTP/ContentNegotiation`, `HTTP/Session`
- Affected files: `DataStack/Database/System/Capabilities/Query/Grammar/Grammar.php:388,425,437`, `DataStack/Database/System/Capabilities/Query/Grammar/Grammar.php:389,426,438`, `DataStack/Persistence/System/Flows/CompileDataQuery/CompileDataQuery.php:70,74,87,95,100`, `HTTP/ContentNegotiation/System/Capabilities/Formats/CsvFormat.php:18,21,24,25`, `HTTP/Session/System/Capabilities/Storage/DatabaseSessionStore.php:30,50,58`
- Source finding IDs included: SAI-0076, SAI-0077, SAI-0078, SAI-0085, SAI-0087
- Highest original severity: MEDIUM
- Normalized severity: HIGH
- Why normalized severity is correct: Security/runtime-sensitive evidence defaults to HIGH/BLOCKER until proven otherwise.
- Duplicate handling: primary finding `SAI-0076`; duplicates/overlaps merge into TODO-026.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: yes, see Needs Verification TODOs.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-026.

## CLUSTER-022: Public interface/PHPDoc contract gaps
- Root cause: Public interface/PHPDoc contract gaps.
- Affected units: `CROSS_CUTTING`, `HttpKernelInterface.php`, `Multiple files`, `RuntimeKernelInterface.php`, `components/DeveloperTools/Documentation/Api`, `framework`
- Affected files: `HttpKernelInterface.php:10-13`, `Multiple files`, `RuntimeKernelInterface.php:11-16`, `components/DeveloperTools/Documentation/Api`, `framework/System/PublicSurface/AvaxInterface.php:16-33`, `framework/System/PublicSurface/Console/ConsoleKernelInterface.php:9-15`, `framework/System/PublicSurface/Http/HttpKernelInterface.php:10-13`, `framework/System/PublicSurface/Runtime/RuntimeKernelInterface.php:11-16`, `multiple`
- Source finding IDs included: HTD-0002, OLD-FIX-197, SAI-0019, SAI-0178, SAI-0179, SAI-0180, SAI-0181, SAI-0215, SAI-0216, SAI-0217, SAI-0243, SCR-0002
- Highest original severity: HIGH
- Normalized severity: MEDIUM
- Why normalized severity is correct: Severity follows highest repeated source pattern and remediation blast radius.
- Duplicate handling: primary finding `SAI-0178`; duplicates/overlaps merge into TODO-027.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: yes, see Needs Verification TODOs.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-027.

## CLUSTER-023: Empty stubs/no-op methods and incomplete units
- Root cause: Empty stubs/no-op methods and incomplete units.
- Affected units: `API/DeveloperTools/`, `ConfigureRuntime.php`, `ResetApplicationState.php`, `ShutdownRuntime.php`, `framework`
- Affected files: `API/DeveloperTools/`, `ConfigureRuntime.php:1-9`, `ResetApplicationState.php:28-38`, `ShutdownRuntime.php:16-22`, `framework/System/Configuration/ConfigureRuntime/ConfigureRuntime.php:1-9`, `framework/System/Flows/HandleIncomingHttp/FrameworkRouteRegistrar.php:83-88`, `framework/System/Flows/ResetApplicationState/ResetApplicationState.php:28-38`, `framework/System/Flows/ShutdownRuntime/ShutdownRuntime.php:16-22`
- Source finding IDs included: SAI-0020, SAI-0191, SAI-0192, SAI-0193, SAI-0196, SAI-0227, SAI-0228, SAI-0229
- Highest original severity: MEDIUM
- Normalized severity: MEDIUM
- Why normalized severity is correct: Severity follows highest repeated source pattern and remediation blast radius.
- Duplicate handling: primary finding `SAI-0191`; duplicates/overlaps merge into TODO-028.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: yes, see Needs Verification TODOs.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-028.

## CLUSTER-024: Silent catch-and-continue error handling
- Root cause: Silent catch-and-continue error handling.
- Affected units: `API/ApiBlueprint`, `Config`, `FeatureFlags/PublicSurface/FeatureFlags.php`, `HTTP`, `PreCommit.php`, `PreCommit/PreCommit.php`, `Text`, `framework`
- Affected files: `API/ApiBlueprint/System/Capabilities/Rpc/RpcExecutor.php:30`, `Config/System/PublicSurface/shortcuts.php:18`, `FeatureFlags/PublicSurface/FeatureFlags.php:23-27`, `HTTP/System/Flows/HandleRequest/HandleRequest.php:23`, `PreCommit.php:291`, `PreCommit/PreCommit.php:291-301`, `Text/System/PublicSurface/Text.php:202-207`, `framework/System/Flows/ConvertPhpErrorToThrowable/ConvertPhpErrorToThrowable.php:74-95`
- Source finding IDs included: SAI-0012, SAI-0047, SAI-0049, SAI-0050, SAI-0083, SAI-0149, SAI-0166, SAI-0197
- Highest original severity: MEDIUM
- Normalized severity: MEDIUM
- Why normalized severity is correct: Security/runtime-sensitive evidence defaults to HIGH/BLOCKER until proven otherwise.
- Duplicate handling: primary finding `SAI-0012`; duplicates/overlaps merge into TODO-025.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: yes, see Needs Verification TODOs.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-025.

## CLUSTER-025: Low-risk compat/version/style cleanup
- Root cause: Low-risk compat/version/style cleanup.
- Affected units: `ApplicationWorkflow`, `AvaxVersion.php`, `compat.php`, `framework`
- Affected files: `ApplicationWorkflow/System/PublicSurface/Saga.php:27`, `AvaxVersion.php:9-15`, `compat.php:36-162`, `framework/System/Foundation/Version/AvaxVersion.php:9-15`
- Source finding IDs included: SAI-0127, SAI-0200, SAI-0234, SAI-0235
- Highest original severity: LOW
- Normalized severity: LOW
- Why normalized severity is correct: Severity follows highest repeated source pattern and remediation blast radius.
- Duplicate handling: primary finding `SAI-0127`; duplicates/overlaps merge into TODO-030.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: yes, see Needs Verification TODOs.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-030.

## CLUSTER-027: Global helper service-locator shortcuts
- Root cause: Global helper service-locator shortcuts.
- Affected units: `All shortcuts.php (13 files)`, `HTTP/Security`, `components/Application/Text`
- Affected files: `All shortcuts.php (13 files)`, `HTTP/Security/System/PublicSurface/shortcuts.php:56`, `components/Application/Text/System/PublicSurface/shortcuts.php`
- Source finding IDs included: HTD-0435, SAI-0065, SAI-0080, SCR-0435
- Highest original severity: BLOCKER
- Normalized severity: HIGH
- Why normalized severity is correct: Severity follows highest repeated source pattern and remediation blast radius.
- Duplicate handling: primary finding `SAI-0065`; duplicates/overlaps merge into TODO-019.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: yes, see Needs Verification TODOs.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-019.

## CLUSTER-028: Missing or weak behavior test proof
- Root cause: Missing or weak behavior test proof.
- Affected units: `CROSS_CUTTING`, `components/API/Contracts`, `components/DeveloperTools/DumpDebugger`, `components/HTTP/Client`, `components/Identity/Security`, `components/Integration/ObjectStorage`, `components/Operations/BackgroundProcesses`, `components/Operations/Delivery`, `components/Operations/MemoryLifecycle`, `components/Operations/Realtime`, `components/Operations/RuntimeSupervision`, `components/Security/DataProtection`, `components/Security/Privacy`
- Affected files: `components/API/Contracts`, `components/DeveloperTools/DumpDebugger`, `components/HTTP/Client`, `components/Identity/Security`, `components/Integration/ObjectStorage`, `components/Operations/BackgroundProcesses`, `components/Operations/Delivery`, `components/Operations/MemoryLifecycle`, `components/Operations/Realtime`, `components/Operations/RuntimeSupervision`, `components/Security/DataProtection`, `components/Security/Privacy`, `multiple`
- Source finding IDs included: HTD-0001, HTD-0003, HTD-0004, HTD-0005, HTD-0006, HTD-0007, HTD-0008, HTD-0009, HTD-0010, HTD-0011, HTD-0012, HTD-0013, OLD-FIX-169, OLD-FIX-199, OLD-FIX-206, OLD-FIX-217, OLD-FIX-218, OLD-FIX-219, OLD-FIX-220, OLD-FIX-226, OLD-FIX-230, OLD-FIX-234, OLD-FIX-235, OLD-FIX-236, SAI-0241, SCR-0001, SCR-0003, SCR-0004, SCR-0005, SCR-0006, SCR-0007, SCR-0008, SCR-0009, SCR-0010, SCR-0011, SCR-0012, SCR-0013
- Highest original severity: HIGH
- Normalized severity: MEDIUM
- Why normalized severity is correct: Severity follows highest repeated source pattern and remediation blast radius.
- Duplicate handling: primary finding `SAI-0241`; duplicates/overlaps merge into TODO-021.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: none beyond remediation validation commands.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-021.

## CLUSTER-029: Accepted semantic PHPDoc legacy ratchet
- Root cause: Accepted semantic PHPDoc legacy ratchet.
- Affected units: `components/ and framework/`
- Affected files: `components/ and framework/`
- Source finding IDs included: DR-0666
- Highest original severity: ACCEPTED_YELLOW
- Normalized severity: ACCEPTED_YELLOW
- Why normalized severity is correct: Severity follows highest repeated source pattern and remediation blast radius.
- Duplicate handling: primary finding `DR-0666`; duplicates/overlaps merge into TODO-032.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: none beyond remediation validation commands.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-032.

## CLUSTER-032: Other review findings requiring triage
- Root cause: Other review findings requiring triage.
- Affected units: `API/GraphQL`, `App.php`, `BootDslEngine.php`, `CROSS_CUTTING`, `Cache`, `CacheRegistrar.php`, `Container/Foundation/FrozenContainer.php`, `Container/Foundation/SimpleContainer.php`, `DetectStateLeak.php`, `DiscoverComponents.php`, `FailureBoundary/Capabilities/RunFallbackAction/RunFallbackAction.php`, `FailureBoundary/Capabilities/RunRecoveryAction/RunRecoveryAction.php`, `FailureBoundary/Configuration/FailureBoundaryServiceProvider.php`, `FailureBoundary/PublicSurface/FailureBoundary.php`, `FailureBoundaryServiceProvider.php`, `HTTP`, `HandleException.php`, `InspectStaticState.php`, `PolicyRule.php`, `PreCommit/PreCommitValidator.php` ...
- Affected files: `API/GraphQL/System/Flows/ValidateGraphQLOperation.php:21–24`, `App.php:254`, `BootDslEngine.php:157-206`, `Cache/System/Configuration/CacheRegistrar.php:93-109`, `CacheRegistrar.php:93-109`, `Container/Foundation/FrozenContainer.php:75,184`, `Container/Foundation/SimpleContainer.php:43,136`, `DetectStateLeak.php:10`, `DiscoverComponents.php:20`, `FailureBoundary/Capabilities/RunFallbackAction/RunFallbackAction.php:32`, `FailureBoundary/Capabilities/RunRecoveryAction/RunRecoveryAction.php:38`, `FailureBoundary/Configuration/FailureBoundaryServiceProvider.php:16`, `FailureBoundary/PublicSurface/FailureBoundary.php:22-58`, `FailureBoundaryServiceProvider.php:16`, `HTTP/System/Capabilities/MiddlewarePipeline/RateLimiterMiddleware.php:110`, `HandleException.php:11`, `InspectStaticState.php:10`, `PolicyRule.php:22`, `PreCommit/PreCommitValidator.php:120`, `RunDoctor.php:11` ...
- Source finding IDs included: DR-0667, DR-0668, HTD-0056, HTD-0060, HTD-0103, HTD-0105, HTD-0107, HTD-0108, HTD-0109, HTD-0110, HTD-0114, HTD-0116, HTD-0262, HTD-0359, HTD-0361, HTD-0362, HTD-0363, HTD-0364, HTD-0365, HTD-0366, HTD-0367, HTD-0423, HTD-0434, HTD-0460, HTD-0467, HTD-0476, HTD-0481, HTD-0483, HTD-0490, HTD-0498, HTD-0499, HTD-0500, HTD-0550, HTD-0637, HTD-0638, HTD-0639, HTD-0658, HTD-0659, HTD-0660, HTD-0661 ... (141 total)
- Highest original severity: BLOCKER
- Normalized severity: MEDIUM
- Why normalized severity is correct: Severity follows highest repeated source pattern and remediation blast radius.
- Duplicate handling: primary finding `SAI-0152`; duplicates/overlaps merge into TODO-031.
- Source disagreements: preserved in `extracted-findings.md`; higher severity retained until verification.
- Required verification: yes, see Needs Verification TODOs.
- Remediation shape: one safe batch.
- Becomes TODO(s): TODO-031.

## DR Source Coverage Addendum

These DR source IDs are included in the same clusters after the coverage repair pass.

- CLUSTER-001: DR-0080, DR-0085, DR-0427
- CLUSTER-002: DR-0452, DR-0453
- CLUSTER-004: DR-0147, DR-0304, DR-0305, DR-0412, DR-0413, DR-0416, DR-0665
- CLUSTER-007: DR-0393, DR-0395, DR-0397, DR-0398, DR-0399, DR-0400, DR-0644, DR-0645
- CLUSTER-008: DR-0369, DR-0603
- CLUSTER-009: DR-0048, DR-0049, DR-0050, DR-0051, DR-0052, DR-0053, DR-0054, DR-0055, DR-0098, DR-0099, DR-0100, DR-0101, DR-0103, DR-0119, DR-0120, DR-0122, DR-0123, DR-0124, DR-0126, DR-0128, DR-0135, DR-0140, DR-0142, DR-0143, DR-0144, DR-0146, DR-0150, DR-0151, DR-0158, DR-0159, DR-0160, DR-0164, DR-0165, DR-0169, DR-0174, DR-0175, DR-0177, DR-0179, DR-0182, DR-0183, DR-0184, DR-0185, DR-0189, DR-0190, DR-0192, DR-0193, DR-0195, DR-0196, DR-0198, DR-0199, DR-0201, DR-0203, DR-0205, DR-0206, DR-0226, DR-0227, DR-0232, DR-0233, DR-0234, DR-0235, DR-0236, DR-0237, DR-0239, DR-0240, DR-0241, DR-0242, DR-0243, DR-0244, DR-0245, DR-0246, DR-0247, DR-0248, DR-0249, DR-0250, DR-0251, DR-0252, DR-0266, DR-0267, DR-0268, DR-0273 ...
- CLUSTER-010: DR-0047, DR-0056, DR-0058, DR-0059, DR-0060, DR-0062, DR-0063, DR-0064, DR-0065, DR-0066, DR-0067, DR-0068, DR-0069, DR-0070, DR-0071, DR-0072, DR-0073, DR-0074, DR-0075, DR-0076, DR-0077, DR-0078, DR-0079, DR-0081, DR-0082, DR-0083, DR-0084, DR-0086, DR-0087, DR-0088, DR-0089, DR-0090, DR-0091, DR-0092, DR-0093, DR-0094, DR-0095, DR-0096, DR-0097, DR-0102, DR-0105, DR-0107, DR-0112, DR-0113, DR-0114, DR-0116, DR-0118, DR-0121, DR-0125, DR-0127, DR-0129, DR-0130, DR-0131, DR-0132, DR-0133, DR-0134, DR-0136, DR-0137, DR-0138, DR-0139, DR-0141, DR-0145, DR-0148, DR-0149, DR-0152, DR-0153, DR-0154, DR-0155, DR-0156, DR-0157, DR-0161, DR-0162, DR-0163, DR-0166, DR-0167, DR-0168, DR-0170, DR-0171, DR-0172, DR-0173 ...
- CLUSTER-011: DR-0014, DR-0015, DR-0016, DR-0017, DR-0018, DR-0019, DR-0020, DR-0021, DR-0022, DR-0023, DR-0024, DR-0025, DR-0026, DR-0027, DR-0028, DR-0029, DR-0030, DR-0031, DR-0032, DR-0033, DR-0034, DR-0035, DR-0036, DR-0037, DR-0038
- CLUSTER-012: DR-0040, DR-0041, DR-0042, DR-0043
- CLUSTER-013: DR-0044, DR-0045, DR-0046
- CLUSTER-014: DR-0422, DR-0423, DR-0426, DR-0428, DR-0430, DR-0431, DR-0433, DR-0442, DR-0443, DR-0446, DR-0448, DR-0449, DR-0451, DR-0454, DR-0456, DR-0463, DR-0464, DR-0466, DR-0467, DR-0469, DR-0470, DR-0471, DR-0472, DR-0473, DR-0475, DR-0476, DR-0479, DR-0481, DR-0483, DR-0485, DR-0486, DR-0487, DR-0488, DR-0489, DR-0490, DR-0492, DR-0493, DR-0494, DR-0495, DR-0496, DR-0497, DR-0498, DR-0503, DR-0505, DR-0508, DR-0516, DR-0517, DR-0519, DR-0520, DR-0521, DR-0522, DR-0523, DR-0524, DR-0525, DR-0526, DR-0528, DR-0529, DR-0530, DR-0532, DR-0533, DR-0535, DR-0540, DR-0541, DR-0544, DR-0547, DR-0548, DR-0549, DR-0550, DR-0552, DR-0553, DR-0554, DR-0555, DR-0556, DR-0557, DR-0558, DR-0559, DR-0560, DR-0561, DR-0562, DR-0563 ...
- CLUSTER-015: DR-0425, DR-0429, DR-0432, DR-0434, DR-0437, DR-0438, DR-0439, DR-0440, DR-0441, DR-0444, DR-0445, DR-0447, DR-0450, DR-0455, DR-0457, DR-0458, DR-0459, DR-0460, DR-0462, DR-0465, DR-0474, DR-0478, DR-0480, DR-0502, DR-0504, DR-0506, DR-0507, DR-0509, DR-0510, DR-0511, DR-0512, DR-0513, DR-0514, DR-0515, DR-0518, DR-0527, DR-0531, DR-0534, DR-0536, DR-0537, DR-0538, DR-0539, DR-0542, DR-0543, DR-0545, DR-0546, DR-0576, DR-0580, DR-0587, DR-0602, DR-0612, DR-0624, DR-0626, DR-0648, DR-0649, DR-0656, DR-0657, DR-0658
- CLUSTER-027: DR-0436
- CLUSTER-028: DR-0001, DR-0002, DR-0003, DR-0004, DR-0005, DR-0006, DR-0007, DR-0008, DR-0009, DR-0010, DR-0011, DR-0012, DR-0013
- CLUSTER-032: DR-0057, DR-0061, DR-0104, DR-0106, DR-0108, DR-0109, DR-0110, DR-0111, DR-0115, DR-0117, DR-0263, DR-0360, DR-0362, DR-0363, DR-0364, DR-0365, DR-0366, DR-0367, DR-0368, DR-0424, DR-0435, DR-0461, DR-0468, DR-0477, DR-0482, DR-0484, DR-0491, DR-0499, DR-0500, DR-0501, DR-0551, DR-0638, DR-0639, DR-0640, DR-0659, DR-0660, DR-0661, DR-0662, DR-0663, DR-0664

- CLUSTER-012 remediation-plan addendum: DR-0039

## HTD Global Cross-Cutting Addendum

- CLUSTER-029: HTD-0665
- CLUSTER-031: HTD-0667

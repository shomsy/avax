# Global Strict Code Review Report

Generated: 2026-05-19T23:31:00+02:00

## 1. Executive Summary

- Component units reviewed: 85
- Framework units reviewed: 83
- Strict findings: 665
- Global system-level strict decision: **TARGETED_REDESIGN**
- This report intentionally excludes the full how-to deviation matrix; related HTD IDs live in the how-to deviation files and cross-map.

## 2. Strict Code Review Decision Summary

- KEEP_AND_IMPROVE count: 100
- TARGETED_REDESIGN count: 66
- REWRITE_CANDIDATE count: 0
- NEEDS_DEEPER_AUDIT count: 2

## 3. Top BLOCKER Strict Code Review Findings

| Finding ID | Severity | Unit | Path | Issue | Risk | Source DR |
|---|---|---|---|---|---|---|
| SCR-0602 | BLOCKER | `components/Identity/Auth` | `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php` | Configuration builder is 797 lines (>300). | CONFIGURATION_DI | `DR-0603` |

## 4. Top HIGH Strict Code Review Findings

| Finding ID | Severity | Unit | Path | Issue | Risk | Source DR |
|---|---|---|---|---|---|---|
| SCR-0665 | HIGH | `components/HTTP` | `components/HTTP/System` | Active broken reference: Avax\Components\HTTP\System\Capabilities\MiddlewarePipeline\System\Foundation\Failure\MiddlewareFailure. | PUBLIC_API | `DR-0039` |
| SCR-0016 | HIGH | `components/API/GraphQL` | `components/API/GraphQL` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | CONFIGURATION_DI | `DR-0016` |
| SCR-0017 | HIGH | `components/API/OpenAPI` | `components/API/OpenAPI` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | CONFIGURATION_DI | `DR-0017` |
| SCR-0023 | HIGH | `components/DataStack/DataTransfer` | `components/DataStack/DataTransfer` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | CONFIGURATION_DI | `DR-0023` |
| SCR-0030 | HIGH | `components/Foundation/CallableSerialization` | `components/Foundation/CallableSerialization` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | CONFIGURATION_DI | `DR-0030` |
| SCR-0034 | HIGH | `components/HTTP/Dispatcher` | `components/HTTP/Dispatcher` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | CONFIGURATION_DI | `DR-0034` |
| SCR-0035 | HIGH | `components/HTTP/Security` | `components/HTTP/Security` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | CONFIGURATION_DI | `DR-0035` |
| SCR-0037 | HIGH | `components/Identity/Security` | `components/Identity/Security` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | CONFIGURATION_DI | `DR-0037` |
| SCR-0039 | HIGH | `components/Operations/ApplicationWorkflow` | `components/Operations/ApplicationWorkflow` | Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\CompensationExecutor`. | PUBLIC_API | `DR-0040` |
| SCR-0040 | HIGH | `components/Operations/ApplicationWorkflow` | `components/Operations/ApplicationWorkflow` | Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\IdempotencyStore`. | PUBLIC_API | `DR-0041` |
| SCR-0041 | HIGH | `components/Operations/ApplicationWorkflow` | `components/Operations/ApplicationWorkflow` | Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\SagaState`. | PUBLIC_API | `DR-0042` |
| SCR-0042 | HIGH | `components/Operations/ApplicationWorkflow` | `components/Operations/ApplicationWorkflow` | Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\SagaStep`. | PUBLIC_API | `DR-0043` |
| SCR-0043 | HIGH | `framework/System/Configuration/Builders` | `framework/System/Configuration/Builders/BuildDispatchConfiguredRoute.php:46` | Raw `is_file()` in framework route-dispatch builder is classified MIGRATE_TO_FILESYSTEM. | SECURITY | `DR-0044` |
| SCR-0048 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/PublicSurface/CompiledCache.php:31,59,60,61` | PublicSurface directly instantiates collaborators (4 `new` expressions detected). | PUBLIC_API | `DR-0049` |
| SCR-0049 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/PublicSurface/Cache.php:45,89,100,108` | PublicSurface directly instantiates collaborators (4 `new` expressions detected). | PUBLIC_API | `DR-0050` |
| SCR-0063 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/ClearCompiledCache/ClearCompiledCache.php:31` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0064` |
| SCR-0064 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/ClearCompiledCache/ClearCompiledCache.php:33` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0065` |
| SCR-0065 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/ClearCompiledCache/ClearCompiledCache.php:38` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0066` |
| SCR-0066 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/ReadCompiledCache/ReadCompiledCache.php:27` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0067` |
| SCR-0067 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/ReadCompiledCache/ReadCompiledCache.php:37` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0068` |
| SCR-0068 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/ReadCompiledCache/ReadCompiledCache.php:39` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0069` |
| SCR-0069 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/CompileCache/CompileCache.php:27` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0070` |
| SCR-0070 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/CompileCache/CompileCache.php:36` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0071` |
| SCR-0071 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/CompileCache/CompileCache.php:40` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0072` |
| SCR-0072 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Operations/StoreCachedValue/StoreCachedValue.php:24` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0073` |
| SCR-0073 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Protection/ProtectCacheSource/AcquireCacheStampedeLock.php:23` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0074` |
| SCR-0074 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Protection/ProtectCacheSource/AcquireCacheStampedeLock.php:30` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0075` |
| SCR-0075 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Lifecycle/EvictCachedValue/EvictCachedValue.php:18` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0076` |
| SCR-0076 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Lifecycle/WarmCache/WarmCache.php:21` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0077` |
| SCR-0077 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Lifecycle/RefreshCachedValue/RefreshCachedValue.php:26` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0078` |
| SCR-0078 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Lifecycle/RefreshCachedValue/RefreshCachedValue.php:29` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0079` |
| SCR-0097 | HIGH | `components/Application/Text` | `components/Application/Text/System/PublicSurface/Text.php:29,34,49,54,59,64,69,74...` | PublicSurface directly instantiates collaborators (25 `new` expressions detected). | PUBLIC_API | `DR-0098` |
| SCR-0100 | HIGH | `components/Application/Storage` | `components/Application/Storage/System/PublicSurface/Storage.php:37,56,65,84,93,101,109,117...` | PublicSurface directly instantiates collaborators (11 `new` expressions detected). | PUBLIC_API | `DR-0101` |
| SCR-0117 | HIGH | `components/Application/Validation` | `components/Application/Validation/System/PublicSurface/Validation.php:18` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0118` |
| SCR-0119 | HIGH | `components/Application/Filesystem` | `components/Application/Filesystem/System/PublicSurface/Filesystem.php:39,44,49,54,59,64,69,74...` | PublicSurface directly instantiates collaborators (21 `new` expressions detected). | PUBLIC_API | `DR-0120` |
| SCR-0121 | HIGH | `components/HTTP/ContentNegotiation` | `components/HTTP/ContentNegotiation/System/PublicSurface/ContentNegotiation.php:23,30,44,45,46,47` | PublicSurface directly instantiates collaborators (6 `new` expressions detected). | PUBLIC_API | `DR-0122` |
| SCR-0124 | HIGH | `components/HTTP/Client` | `components/HTTP/Client/System/PublicSurface/HttpClient.php:14` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0125` |
| SCR-0125 | HIGH | `components/HTTP/Client` | `components/HTTP/Client/System/PublicSurface/HttpClient.php:14,19,34` | PublicSurface directly instantiates collaborators (3 `new` expressions detected). | PUBLIC_API | `DR-0126` |
| SCR-0126 | HIGH | `components/HTTP/Request` | `components/HTTP/Request/System/PublicSurface/Request.php:33` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0127` |
| SCR-0132 | HIGH | `components/HTTP/Request` | `components/HTTP/Request/System/Flows/CreateRequestFromGlobals/CreateRequestFromGlobals.php:41` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0133` |
| SCR-0138 | HIGH | `components/HTTP/Session` | `components/HTTP/Session/System/PublicSurface/Session.php:30` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0139` |

## Security-Sensitive Strict Findings

| Finding ID | Severity | Unit | Path | Issue | Risk | Source DR |
|---|---|---|---|---|---|---|
| SCR-0602 | BLOCKER | `components/Identity/Auth` | `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php` | Configuration builder is 797 lines (>300). | CONFIGURATION_DI | `DR-0603` |
| SCR-0043 | HIGH | `framework/System/Configuration/Builders` | `framework/System/Configuration/Builders/BuildDispatchConfiguredRoute.php:46` | Raw `is_file()` in framework route-dispatch builder is classified MIGRATE_TO_FILESYSTEM. | SECURITY | `DR-0044` |
| SCR-0354 | HIGH | `components/Identity/ExternalIdentity` | `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/UpdateClient/UpdateClient.php:31` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0355` |
| SCR-0355 | HIGH | `components/Identity/ExternalIdentity` | `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/ReadWorkloadIdentities/ReadWorkloadIdentities.php:25` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0356` |
| SCR-0369 | HIGH | `components/Identity/Auth` | `components/Identity/Auth/System/Flows/Register/CreateRegisteredUser.php:26` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0370` |
| SCR-0372 | HIGH | `components/Identity/Auth` | `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/DeprovisionUser/DeprovisionUser.php:38` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0373` |
| SCR-0373 | HIGH | `components/Identity/Auth` | `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/SuspendUser/SuspendUser.php:38` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0374` |
| SCR-0374 | HIGH | `components/Identity/Auth` | `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/ReactivateUser/ReactivateUser.php:23` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0375` |
| SCR-0375 | HIGH | `components/Identity/Auth` | `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ReadGroups/ReadScimGroups.php:22` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0376` |
| SCR-0376 | HIGH | `components/Identity/Auth` | `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ReadUsers/ReadScimUsers.php:30` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0377` |
| SCR-0382 | HIGH | `components/Identity/Tokens` | `components/Identity/Tokens/System/Flows/AuthorizeToken/AuthorizeTokenRequest.php:17` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0383` |
| SCR-0383 | HIGH | `components/Identity/Tokens` | `components/Identity/Tokens/System/Flows/ExchangeToken/ExchangeAuthorizationCode.php:24` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0384` |
| SCR-0384 | HIGH | `components/Identity/Tokens` | `components/Identity/Tokens/System/Flows/ExchangeToken/ExchangeAuthorizationCode.php:25` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0385` |
| SCR-0385 | HIGH | `components/Identity/Tokens` | `components/Identity/Tokens/System/Flows/ExchangeToken/ExchangeAuthorizationCode.php:33` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0386` |
| SCR-0572 | HIGH | `components/Identity/ExternalIdentity` | `components/Identity/ExternalIdentity/System/Capabilities/OAuth/OAuth.php:41` | Constructor has 14 parameters. | MAINTAINABILITY | `DR-0573` |
| SCR-0580 | HIGH | `components/Identity/ExternalIdentity` | `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/PushAuthorizationRequest/PushAuthorizationRequestData.php:18` | Constructor has 12 parameters. | MAINTAINABILITY | `DR-0581` |
| SCR-0583 | HIGH | `components/Identity/ExternalIdentity` | `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/OAuthTokenGrant.php:24` | Constructor has 13 parameters. | MAINTAINABILITY | `DR-0584` |
| SCR-0584 | HIGH | `components/Identity/ExternalIdentity` | `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Elements/OAuthClient.php:48` | Constructor has 23 parameters. | MAINTAINABILITY | `DR-0585` |
| SCR-0585 | HIGH | `components/Identity/ExternalIdentity` | `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Elements/AuthorizationCodeRecord.php:19` | Constructor has 13 parameters. | MAINTAINABILITY | `DR-0586` |
| SCR-0589 | HIGH | `components/Identity/ExternalIdentity` | `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/IntrospectToken/TokenIntrospection.php:20` | Constructor has 13 parameters. | MAINTAINABILITY | `DR-0590` |
| SCR-0590 | HIGH | `components/Identity/ExternalIdentity` | `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/UpdateClient/UpdateClientData.php:46` | Constructor has 19 parameters. | MAINTAINABILITY | `DR-0591` |
| SCR-0593 | HIGH | `components/Identity/ExternalIdentity` | `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/RegisterClient/RegisterClientData.php:46` | Constructor has 18 parameters. | MAINTAINABILITY | `DR-0594` |
| SCR-0599 | HIGH | `components/Identity/Auth` | `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthExternalIdentityGraph.php:92` | Constructor has 27 parameters. | MAINTAINABILITY | `DR-0600` |
| SCR-0600 | HIGH | `components/Identity/Auth` | `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:136` | Constructor has 43 parameters. | MAINTAINABILITY | `DR-0601` |
| SCR-0603 | HIGH | `components/Identity/Auth` | `components/Identity/Auth/System/Flows/ChangePassword/ChangePassword.php:34` | Constructor has 12 parameters. | MAINTAINABILITY | `DR-0604` |
| SCR-0609 | HIGH | `components/Identity/Auth` | `components/Identity/Auth/System/Flows/CheckAuthentication/AuthenticateRequest/AuthenticationContext.php:16` | Constructor has 12 parameters. | MAINTAINABILITY | `DR-0610` |
| SCR-0613 | HIGH | `components/Identity/Auth` | `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/SCIM.php:38` | Constructor has 12 parameters. | MAINTAINABILITY | `DR-0614` |
| SCR-0614 | HIGH | `components/Identity/Auth` | `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Directories/ScimDirectory.php:17` | Constructor has 13 parameters. | MAINTAINABILITY | `DR-0615` |
| SCR-0044 | MEDIUM | `framework/System/Capabilities/FailureBoundary` | `framework/System/Capabilities/FailureBoundary/Capabilities/WriteCompiledFailurePolicies/WriteCompiledFailurePolicies.php:30-34; CompileFailurePolicies.php:39; ReadCompiledFailurePolicies.php:25-29; CompiledMethodPolicy.php:116` | FailureBoundary compiled policy files use raw file operations classified NEEDS_DESIGN_DECISION. | SECURITY | `DR-0045` |
| SCR-0045 | MEDIUM | `components/DataStack/DataTransfer` | `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/CompileClassAttributes.php; components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/CompileDataShapeSchema.php` | DataTransfer compiled metadata capabilities use raw file/directory operations classified NEEDS_DESIGN_DECISION. | SECURITY | `DR-0046` |

## Runtime Safety Strict Findings

| Finding ID | Severity | Unit | Path | Issue | Risk | Source DR |
|---|---|---|---|---|---|---|
| SCR-0602 | BLOCKER | `components/Identity/Auth` | `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php` | Configuration builder is 797 lines (>300). | CONFIGURATION_DI | `DR-0603` |
| SCR-0016 | HIGH | `components/API/GraphQL` | `components/API/GraphQL` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | CONFIGURATION_DI | `DR-0016` |
| SCR-0017 | HIGH | `components/API/OpenAPI` | `components/API/OpenAPI` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | CONFIGURATION_DI | `DR-0017` |
| SCR-0023 | HIGH | `components/DataStack/DataTransfer` | `components/DataStack/DataTransfer` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | CONFIGURATION_DI | `DR-0023` |
| SCR-0030 | HIGH | `components/Foundation/CallableSerialization` | `components/Foundation/CallableSerialization` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | CONFIGURATION_DI | `DR-0030` |
| SCR-0034 | HIGH | `components/HTTP/Dispatcher` | `components/HTTP/Dispatcher` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | CONFIGURATION_DI | `DR-0034` |
| SCR-0035 | HIGH | `components/HTTP/Security` | `components/HTTP/Security` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | CONFIGURATION_DI | `DR-0035` |
| SCR-0037 | HIGH | `components/Identity/Security` | `components/Identity/Security` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | CONFIGURATION_DI | `DR-0037` |
| SCR-0063 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/ClearCompiledCache/ClearCompiledCache.php:31` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0064` |
| SCR-0064 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/ClearCompiledCache/ClearCompiledCache.php:33` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0065` |
| SCR-0065 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/ClearCompiledCache/ClearCompiledCache.php:38` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0066` |
| SCR-0066 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/ReadCompiledCache/ReadCompiledCache.php:27` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0067` |
| SCR-0067 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/ReadCompiledCache/ReadCompiledCache.php:37` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0068` |
| SCR-0068 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/ReadCompiledCache/ReadCompiledCache.php:39` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0069` |
| SCR-0069 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/CompileCache/CompileCache.php:27` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0070` |
| SCR-0070 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/CompileCache/CompileCache.php:36` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0071` |
| SCR-0071 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/CompileCache/CompileCache.php:40` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0072` |
| SCR-0072 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Operations/StoreCachedValue/StoreCachedValue.php:24` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0073` |
| SCR-0073 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Protection/ProtectCacheSource/AcquireCacheStampedeLock.php:23` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0074` |
| SCR-0074 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Protection/ProtectCacheSource/AcquireCacheStampedeLock.php:30` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0075` |
| SCR-0075 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Lifecycle/EvictCachedValue/EvictCachedValue.php:18` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0076` |
| SCR-0076 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Lifecycle/WarmCache/WarmCache.php:21` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0077` |
| SCR-0077 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Lifecycle/RefreshCachedValue/RefreshCachedValue.php:26` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0078` |
| SCR-0078 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Lifecycle/RefreshCachedValue/RefreshCachedValue.php:29` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0079` |
| SCR-0117 | HIGH | `components/Application/Validation` | `components/Application/Validation/System/PublicSurface/Validation.php:18` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0118` |
| SCR-0124 | HIGH | `components/HTTP/Client` | `components/HTTP/Client/System/PublicSurface/HttpClient.php:14` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0125` |
| SCR-0126 | HIGH | `components/HTTP/Request` | `components/HTTP/Request/System/PublicSurface/Request.php:33` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0127` |
| SCR-0132 | HIGH | `components/HTTP/Request` | `components/HTTP/Request/System/Flows/CreateRequestFromGlobals/CreateRequestFromGlobals.php:41` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0133` |
| SCR-0138 | HIGH | `components/HTTP/Session` | `components/HTTP/Session/System/PublicSurface/Session.php:30` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0139` |
| SCR-0144 | HIGH | `components/HTTP/Router` | `components/HTTP/Router/System/PublicSurface/Router.php:46` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0145` |

## Public API / PublicSurface Strict Findings

| Finding ID | Severity | Unit | Path | Issue | Risk | Source DR |
|---|---|---|---|---|---|---|
| SCR-0039 | HIGH | `components/Operations/ApplicationWorkflow` | `components/Operations/ApplicationWorkflow` | Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\CompensationExecutor`. | PUBLIC_API | `DR-0040` |
| SCR-0040 | HIGH | `components/Operations/ApplicationWorkflow` | `components/Operations/ApplicationWorkflow` | Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\IdempotencyStore`. | PUBLIC_API | `DR-0041` |
| SCR-0041 | HIGH | `components/Operations/ApplicationWorkflow` | `components/Operations/ApplicationWorkflow` | Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\SagaState`. | PUBLIC_API | `DR-0042` |
| SCR-0042 | HIGH | `components/Operations/ApplicationWorkflow` | `components/Operations/ApplicationWorkflow` | Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\SagaStep`. | PUBLIC_API | `DR-0043` |
| SCR-0048 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/PublicSurface/CompiledCache.php:31,59,60,61` | PublicSurface directly instantiates collaborators (4 `new` expressions detected). | PUBLIC_API | `DR-0049` |
| SCR-0049 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/PublicSurface/Cache.php:45,89,100,108` | PublicSurface directly instantiates collaborators (4 `new` expressions detected). | PUBLIC_API | `DR-0050` |
| SCR-0097 | HIGH | `components/Application/Text` | `components/Application/Text/System/PublicSurface/Text.php:29,34,49,54,59,64,69,74...` | PublicSurface directly instantiates collaborators (25 `new` expressions detected). | PUBLIC_API | `DR-0098` |
| SCR-0100 | HIGH | `components/Application/Storage` | `components/Application/Storage/System/PublicSurface/Storage.php:37,56,65,84,93,101,109,117...` | PublicSurface directly instantiates collaborators (11 `new` expressions detected). | PUBLIC_API | `DR-0101` |
| SCR-0117 | HIGH | `components/Application/Validation` | `components/Application/Validation/System/PublicSurface/Validation.php:18` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0118` |
| SCR-0119 | HIGH | `components/Application/Filesystem` | `components/Application/Filesystem/System/PublicSurface/Filesystem.php:39,44,49,54,59,64,69,74...` | PublicSurface directly instantiates collaborators (21 `new` expressions detected). | PUBLIC_API | `DR-0120` |
| SCR-0121 | HIGH | `components/HTTP/ContentNegotiation` | `components/HTTP/ContentNegotiation/System/PublicSurface/ContentNegotiation.php:23,30,44,45,46,47` | PublicSurface directly instantiates collaborators (6 `new` expressions detected). | PUBLIC_API | `DR-0122` |
| SCR-0124 | HIGH | `components/HTTP/Client` | `components/HTTP/Client/System/PublicSurface/HttpClient.php:14` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0125` |
| SCR-0125 | HIGH | `components/HTTP/Client` | `components/HTTP/Client/System/PublicSurface/HttpClient.php:14,19,34` | PublicSurface directly instantiates collaborators (3 `new` expressions detected). | PUBLIC_API | `DR-0126` |
| SCR-0126 | HIGH | `components/HTTP/Request` | `components/HTTP/Request/System/PublicSurface/Request.php:33` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0127` |
| SCR-0138 | HIGH | `components/HTTP/Session` | `components/HTTP/Session/System/PublicSurface/Session.php:30` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0139` |
| SCR-0139 | HIGH | `components/HTTP/Session` | `components/HTTP/Session/System/PublicSurface/Session.php:30,91,92,129,130` | PublicSurface directly instantiates collaborators (5 `new` expressions detected). | PUBLIC_API | `DR-0140` |
| SCR-0142 | HIGH | `components/HTTP` | `components/HTTP/System/PublicSurface/Response.php:38,46,51` | PublicSurface directly instantiates collaborators (3 `new` expressions detected). | PUBLIC_API | `DR-0143` |
| SCR-0143 | HIGH | `components/HTTP/SecureRequest` | `components/HTTP/SecureRequest/System/PublicSurface/SecureRequest.php:80,87,90,99,108,116` | PublicSurface directly instantiates collaborators (6 `new` expressions detected). | PUBLIC_API | `DR-0144` |
| SCR-0144 | HIGH | `components/HTTP/Router` | `components/HTTP/Router/System/PublicSurface/Router.php:46` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0145` |
| SCR-0145 | HIGH | `components/HTTP/Router` | `components/HTTP/Router/System/PublicSurface/Router.php:46,49,107,117,130` | PublicSurface directly instantiates collaborators (5 `new` expressions detected). | PUBLIC_API | `DR-0146` |
| SCR-0147 | HIGH | `components/Operations/Events` | `components/Operations/Events/System/PublicSurface/Events.php:31` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0148` |
| SCR-0148 | HIGH | `components/Operations/Events` | `components/Operations/Events/System/PublicSurface/Events.php:32` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0149` |
| SCR-0149 | HIGH | `components/Operations/Events` | `components/Operations/Events/System/PublicSurface/Events.php:18,31,32,39` | PublicSurface directly instantiates collaborators (4 `new` expressions detected). | PUBLIC_API | `DR-0150` |
| SCR-0156 | HIGH | `components/Operations/Notifications` | `components/Operations/Notifications/System/PublicSurface/Notifier.php:20` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0157` |
| SCR-0158 | HIGH | `components/Operations/Tasks` | `components/Operations/Tasks/System/PublicSurface/Tasks.php:20,25,30,38,43,51,61` | PublicSurface directly instantiates collaborators (7 `new` expressions detected). | PUBLIC_API | `DR-0159` |
| SCR-0163 | HIGH | `components/Operations/Realtime` | `components/Operations/Realtime/System/PublicSurface/Realtime.php:22,31,51` | PublicSurface directly instantiates collaborators (3 `new` expressions detected). | PUBLIC_API | `DR-0164` |
| SCR-0164 | HIGH | `components/Operations/Delivery` | `components/Operations/Delivery/System/PublicSurface/Delivery.php:16,21,26,31` | PublicSurface directly instantiates collaborators (4 `new` expressions detected). | PUBLIC_API | `DR-0165` |
| SCR-0168 | HIGH | `components/Operations/Concurrency` | `components/Operations/Concurrency/System/PublicSurface/Concurrency.php:56,87,95` | PublicSurface directly instantiates collaborators (3 `new` expressions detected). | PUBLIC_API | `DR-0169` |
| SCR-0175 | HIGH | `components/Operations/Queue` | `components/Operations/Queue/System/PublicSurface/Tasks.php:40` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0176` |
| SCR-0176 | HIGH | `components/Operations/Queue` | `components/Operations/Queue/System/PublicSurface/Tasks.php:14,20,26,40` | PublicSurface directly instantiates collaborators (4 `new` expressions detected). | PUBLIC_API | `DR-0177` |

## Performance/Memory Strict Findings

| Finding ID | Severity | Unit | Path | Issue | Risk | Source DR |
|---|---|---|---|---|---|---|
| SCR-0063 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/ClearCompiledCache/ClearCompiledCache.php:31` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0064` |
| SCR-0064 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/ClearCompiledCache/ClearCompiledCache.php:33` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0065` |
| SCR-0065 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/ClearCompiledCache/ClearCompiledCache.php:38` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0066` |
| SCR-0066 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/ReadCompiledCache/ReadCompiledCache.php:27` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0067` |
| SCR-0067 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/ReadCompiledCache/ReadCompiledCache.php:37` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0068` |
| SCR-0068 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/ReadCompiledCache/ReadCompiledCache.php:39` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0069` |
| SCR-0069 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/CompileCache/CompileCache.php:27` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0070` |
| SCR-0070 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/CompileCache/CompileCache.php:36` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0071` |
| SCR-0071 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Compiled/CompileCache/CompileCache.php:40` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0072` |
| SCR-0072 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Operations/StoreCachedValue/StoreCachedValue.php:24` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0073` |
| SCR-0073 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Protection/ProtectCacheSource/AcquireCacheStampedeLock.php:23` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0074` |
| SCR-0074 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Protection/ProtectCacheSource/AcquireCacheStampedeLock.php:30` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0075` |
| SCR-0075 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Lifecycle/EvictCachedValue/EvictCachedValue.php:18` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0076` |
| SCR-0076 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Lifecycle/WarmCache/WarmCache.php:21` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0077` |
| SCR-0077 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Lifecycle/RefreshCachedValue/RefreshCachedValue.php:26` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0078` |
| SCR-0078 | HIGH | `components/Application/Cache` | `components/Application/Cache/System/Flows/Lifecycle/RefreshCachedValue/RefreshCachedValue.php:29` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0079` |
| SCR-0117 | HIGH | `components/Application/Validation` | `components/Application/Validation/System/PublicSurface/Validation.php:18` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0118` |
| SCR-0124 | HIGH | `components/HTTP/Client` | `components/HTTP/Client/System/PublicSurface/HttpClient.php:14` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0125` |
| SCR-0126 | HIGH | `components/HTTP/Request` | `components/HTTP/Request/System/PublicSurface/Request.php:33` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0127` |
| SCR-0132 | HIGH | `components/HTTP/Request` | `components/HTTP/Request/System/Flows/CreateRequestFromGlobals/CreateRequestFromGlobals.php:41` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0133` |
| SCR-0138 | HIGH | `components/HTTP/Session` | `components/HTTP/Session/System/PublicSurface/Session.php:30` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0139` |
| SCR-0144 | HIGH | `components/HTTP/Router` | `components/HTTP/Router/System/PublicSurface/Router.php:46` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0145` |
| SCR-0147 | HIGH | `components/Operations/Events` | `components/Operations/Events/System/PublicSurface/Events.php:31` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0148` |
| SCR-0148 | HIGH | `components/Operations/Events` | `components/Operations/Events/System/PublicSurface/Events.php:32` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0149` |
| SCR-0151 | HIGH | `components/Operations/Events` | `components/Operations/Events/System/Flows/CompileEventListeners/CompileEventListeners.php:36` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0152` |
| SCR-0152 | HIGH | `components/Operations/Events` | `components/Operations/Events/System/Flows/RegisterEventListeners/EventListenerDsl.php:28` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0153` |
| SCR-0156 | HIGH | `components/Operations/Notifications` | `components/Operations/Notifications/System/PublicSurface/Notifier.php:20` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0157` |
| SCR-0169 | HIGH | `components/Operations/Concurrency` | `components/Operations/Concurrency/System/Capabilities/RunWithFibers/FiberTaskRuntime.php:22` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0170` |
| SCR-0170 | HIGH | `components/Operations/Concurrency` | `components/Operations/Concurrency/System/Capabilities/RunWithFibers/FiberTaskRuntime.php:23` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0171` |
| SCR-0171 | HIGH | `components/Operations/Concurrency` | `components/Operations/Concurrency/System/Capabilities/ChooseTaskRuntime/ChooseTaskRuntime.php:26` | Constructor default parameter instantiates a dependency. | CONFIGURATION_DI | `DR-0172` |

## Test Quality Strict Findings

| Finding ID | Severity | Unit | Path | Issue | Risk | Source DR |
|---|---|---|---|---|---|---|
| SCR-0001 | MEDIUM | `components/API/Contracts` | `components/API/Contracts` | No component-specific tests detected under tests/. | TEST_PROOF | `DR-0001` |
| SCR-0002 | MEDIUM | `components/DeveloperTools/Documentation/Api` | `components/DeveloperTools/Documentation/Api` | No component-specific tests detected under tests/. | TEST_PROOF | `DR-0002` |
| SCR-0003 | MEDIUM | `components/DeveloperTools/DumpDebugger` | `components/DeveloperTools/DumpDebugger` | No component-specific tests detected under tests/. | TEST_PROOF | `DR-0003` |
| SCR-0004 | MEDIUM | `components/HTTP/Client` | `components/HTTP/Client` | No component-specific tests detected under tests/. | TEST_PROOF | `DR-0004` |
| SCR-0005 | MEDIUM | `components/Identity/Security` | `components/Identity/Security` | No component-specific tests detected under tests/. | TEST_PROOF | `DR-0005` |
| SCR-0006 | MEDIUM | `components/Integration/ObjectStorage` | `components/Integration/ObjectStorage` | No component-specific tests detected under tests/. | TEST_PROOF | `DR-0006` |
| SCR-0007 | MEDIUM | `components/Operations/BackgroundProcesses` | `components/Operations/BackgroundProcesses` | No component-specific tests detected under tests/. | TEST_PROOF | `DR-0007` |
| SCR-0008 | MEDIUM | `components/Operations/Delivery` | `components/Operations/Delivery` | No component-specific tests detected under tests/. | TEST_PROOF | `DR-0008` |
| SCR-0009 | MEDIUM | `components/Operations/MemoryLifecycle` | `components/Operations/MemoryLifecycle` | No component-specific tests detected under tests/. | TEST_PROOF | `DR-0009` |
| SCR-0010 | MEDIUM | `components/Operations/Realtime` | `components/Operations/Realtime` | No component-specific tests detected under tests/. | TEST_PROOF | `DR-0010` |
| SCR-0011 | MEDIUM | `components/Operations/RuntimeSupervision` | `components/Operations/RuntimeSupervision` | No component-specific tests detected under tests/. | TEST_PROOF | `DR-0011` |
| SCR-0012 | MEDIUM | `components/Security/DataProtection` | `components/Security/DataProtection` | No component-specific tests detected under tests/. | TEST_PROOF | `DR-0012` |
| SCR-0013 | MEDIUM | `components/Security/Privacy` | `components/Security/Privacy` | No component-specific tests detected under tests/. | TEST_PROOF | `DR-0013` |

## 12. Units That Appear Clean Under Strict Review

- Criteria: no strict finding in this pass, tests detected, HIGH discovery confidence, and structural gates passed. This is not a production GREEN claim.

- `RUC-010` `components/Application/Facade`
- `RUC-018` `components/CLI`
- `RUC-034` `components/HTTP/ApiVersioning`
- `RUC-039` `components/HTTP/Middleware`
- `RUC-077` `components/Presentation/View`
- `RUC-078` `components/Security`
- `RUC-081` `components/Security/Hashing`
- `RUF-001` `framework/System`
- `RUF-002` `framework/System/Capabilities`
- `RUF-004` `framework/System/Capabilities/ComponentManifest`
- `RUF-005` `framework/System/Capabilities/ComponentRegistry`
- `RUF-006` `framework/System/Capabilities/ConfigExplanation`
- `RUF-007` `framework/System/Capabilities/ConfigValidation`
- `RUF-008` `framework/System/Capabilities/Configuration`
- `RUF-013` `framework/System/Capabilities/Health`
- `RUF-014` `framework/System/Capabilities/HealthCheck`
- `RUF-015` `framework/System/Capabilities/MetadataWarmup`
- `RUF-017` `framework/System/Capabilities/Queue`
- `RUF-018` `framework/System/Capabilities/RequestScope`
- `RUF-020` `framework/System/Capabilities/ResponseNormalization`
- `RUF-021` `framework/System/Capabilities/RouteIntelligence`
- `RUF-022` `framework/System/Capabilities/Routing`
- `RUF-024` `framework/System/Capabilities/RuntimeBoundary`
- `RUF-027` `framework/System/Capabilities/RuntimeTimeline`
- `RUF-030` `framework/System/Capabilities/StateReset`
- `RUF-031` `framework/System/Capabilities/SystemDesign`
- `RUF-032` `framework/System/Capabilities/TracingTimeline`
- `RUF-033` `framework/System/Capabilities/WorkerManagement`
- `RUF-034` `framework/System/Configuration`
- `RUF-040` `framework/System/Configuration/LoadConfiguration`
- `RUF-041` `framework/System/Configuration/RegisterComponents`
- `RUF-042` `framework/System/Flows`
- `RUF-043` `framework/System/Flows/AuditContainerScope`
- `RUF-045` `framework/System/Flows/CheckRuntimeIsolation`
- `RUF-047` `framework/System/Flows/DescribeDependency`
- `RUF-048` `framework/System/Flows/DetectRouteConflict`
- `RUF-049` `framework/System/Flows/DetectStateLeak`
- `RUF-050` `framework/System/Flows/DiscoverComponents`
- `RUF-051` `framework/System/Flows/ExplainConfig`
- `RUF-052` `framework/System/Flows/ExplainContainerResolution`
- `RUF-053` `framework/System/Flows/ExplainRouteMatch`
- `RUF-054` `framework/System/Flows/HandleException`
- `RUF-056` `framework/System/Flows/HandleRuntimeFailure`
- `RUF-057` `framework/System/Flows/HandleWorkerRequest`
- `RUF-058` `framework/System/Flows/InspectStaticState`
- `RUF-059` `framework/System/Flows/ListComponents`
- `RUF-060` `framework/System/Flows/ListContainerBindings`
- `RUF-061` `framework/System/Flows/ListRoutes`
- `RUF-062` `framework/System/Flows/RegisterHealthRoutes`
- `RUF-063` `framework/System/Flows/ResetApplicationState`
- `RUF-065` `framework/System/Flows/RunConsoleCommand`
- `RUF-067` `framework/System/Flows/ShutdownRuntime`
- `RUF-068` `framework/System/Flows/StartWorker`
- `RUF-069` `framework/System/Flows/ValidateConfig`
- `RUF-070` `framework/System/Flows/VerifyRequestScopeWasClosed`
- `RUF-071` `framework/System/Flows/VerifyResetWasExecuted`
- `RUF-072` `framework/System/Foundation`
- `RUF-073` `framework/System/Foundation/Environment`
- `RUF-074` `framework/System/Foundation/Exception`
- `RUF-075` `framework/System/Foundation/Failure`
- `RUF-076` `framework/System/Foundation/Paths`
- `RUF-077` `framework/System/Foundation/Result`
- `RUF-078` `framework/System/Foundation/Time`
- `RUF-079` `framework/System/Foundation/Version`
- `RUF-081` `framework/System/PublicSurface/Console`
- `RUF-082` `framework/System/PublicSurface/Http`
- `RUF-083` `framework/System/PublicSurface/Runtime`

## 13. Units Needing Deeper Audit

- `RUC-028` `components/DeveloperTools/DumpDebugger` — low confidence or missing tests/evidence for strict approval.
- `RUC-055` `components/Integration/ObjectStorage` — low confidence or missing tests/evidence for strict approval.

## 14. Global System-Level Strict Decision

Decision: **TARGETED_REDESIGN**.
Reason: unresolved BLOCKER/HIGH strict findings exist, especially DI/runtime/PublicSurface/large-unit findings, so global KEEP_AND_IMPROVE would be false optimism.

## Reconciled Finding Addendum

- `SCR-0665` maps `DR-0039` from the discipline remediation-plan/fix-this reconciliation into the strict review set because it was absent from per-unit discipline review files but present in canonical remediation evidence.


## Validation Command Summary

Sandbox note: initial sandbox attempts for `composer validate --no-check-publish`, `php tooling/refactor/check-component-suite-structure.php`, and `php tooling/refactor/check-direct-instantiation.php` failed with Docker socket permission denial. The same validation was rerun with approved escalation through the project wrapper.

| Command | Status | Evidence summary | Impact |
|---|---|---|---|
| `composer validate --no-check-publish` | PASS | `./composer.json is valid` | Composer metadata valid. |
| `composer dump-autoload -o` | PASS_WITH_WARNING | Generated optimized autoload files containing 9346 classes; warning: `framework/System/Foundation/compat.php` class `xhp_` skipped for PSR-4 mismatch. | Autoload generated; warning remains evidence, not GREEN proof. |
| `php tooling/refactor/check-component-suite-structure.php` | PASS | `PASS` | Structure gate passed. |
| `php tooling/refactor/check-duplicate-owners.php` | PASS | `PASS` | Duplicate owner gate passed. |
| `php tooling/refactor/check-namespace-drift.php` | PASS | `PASS` | Namespace drift gate passed. |
| `php tooling/refactor/check-public-surface.php` | PASS | `PASS` | Public surface scanner passed; semantic PublicSurface findings still remain from strict review. |
| `php tooling/refactor/check-runtime-composition-leaks.php` | PASS | `PASS` | Runtime composition leak gate passed. |
| `php tooling/governance/check-governance-index-current.php` | PASS | `GREEN: Governance index is current.` | Governance index current. |
| `php tooling/governance/check-root-evidence-hygiene.php` | PASS | `GREEN: Root evidence hygiene PASSED.` | Root evidence hygiene passed. |
| `bash verify-governance.sh .` | PASS | `Governance Verified: FULL_GREEN_EXECUTABLE_GOVERNANCE_RUNTIME_READY`; script updated generated governance event/provenance files. | Governance harness passed; generated side effects were not staged. |
| `php tooling/refactor/check-direct-instantiation.php` | FAIL_EXPECTED_FINDINGS | `FAIL`, 761 output lines, constructor/default/fallback instantiation findings across framework/components. | Supports SCR/HTD DI/runtime findings. |
| `php tooling/refactor/check-constructor-bloat.php` | FAIL_EXPECTED_FINDINGS | `FAIL`, 460 output lines, CHECK/WARNING constructor arity findings including Runtime and Identity/Auth. | Supports complexity/DI findings. |
| `php tooling/refactor/check-service-provider-coverage.php` | FAIL_EXPECTED_FINDINGS | `FAIL`, 25 missing component ServiceProvider reports. | Supports ServiceProvider how-to deviations. |
| `php tooling/refactor/check-broken-reference-semantics.php` | FAIL_EXPECTED_FINDINGS | `FAIL`, 5 active broken references. | Supports broken reference strict/how-to findings, including DR-0039 reconciliation. |
| `php tooling/governance/check-large-unit-thresholds.php` | FAIL_EXPECTED_FINDINGS | 3887 scanned, 107 findings, 1 BLOCKER (`AuthBuilder`), 106 REVIEW. | Supports large-unit and AuthBuilder BLOCKER findings. |

No production code, tests, composer files, autoload files, or `fix-this.md` were changed by this review pass. Validation failures above are the reviewed quality findings, not accidental remediation failures.

# Recovered Components Taxonomy Report

- Date: 2026-05-01 00:05:47
- Mode: APPLY
- Status: OK

## Operations

- MOVE `components/FeatureFlags` -> `components/Application/FeatureFlags` | feature flags belong to Application suite
- MOVE `components/Pipeline` -> `components/Application/Pipeline` | pipeline belongs to Application suite
- MOVE `components/ApiVersioning` -> `components/HTTP/ApiVersioning` | API versioning is HTTP protocol concern
- MOVE `components/AfterResponse` -> `components/HTTP/AfterResponse` | after response is HTTP protocol concern
- MOVE `components/ContentNegotiation` -> `components/HTTP/ContentNegotiation` | content negotiation is HTTP protocol
  concern
- MOVE `components/Concurrency` -> `components/Operations/Concurrency` | concurrency belongs to Operations suite
- MOVE `components/Realtime` -> `components/Operations/Realtime` | realtime belongs to Operations suite
- MOVE `components/MessageBus` -> `components/Operations/MessageBus` | message bus belongs to Operations suite
- MOVE `components/Idempotency` -> `components/Operations/Resilience/System/Capabilities/Idempotency` | idempotency is
  resilience capability
- MOVE `components/Fallback` -> `components/Operations/Resilience/System/Capabilities/Fallback` | fallback is resilience
  capability
- MOVE `components/TaskDispatch` -> `components/Operations/Queue/System/Capabilities/TaskDispatch` | task dispatch is
  queue capability
- MOVE `components/Orchestration` -> `components/Operations/ApplicationWorkflow/System/Capabilities/Orchestration` |
  orchestration belongs to workflow capability
- MERGE `components/Security` -> `components/Security/System` | Security is now a top-level suite
- MERGE `components/Policy` -> `components/Identity/Access/System/Capabilities/Policy` | policy is access capability
- MOVE `components/JwtAuth` -> `components/Identity/Tokens/System/Capabilities/JwtAuth` | JWT is token capability
- MOVE `components/ScalingReadiness` -> `components/DeveloperTools/Diagnostics/System/Capabilities/ScalingReadiness` |
  scaling diagnostics belongs to DeveloperTools
- MOVE `components/QueryGovernance` -> `components/DataStack/Database/System/Capabilities/QueryGovernance` | query
  governance is database capability
- MOVE `components/ContractTesting` -> `components/DeveloperTools/Testing/System/Capabilities/ContractTesting` |
  contract testing belongs to DeveloperTools
- MOVE `components/EnvironmentAwareness` -> `components/Application/Config/System/Capabilities/EnvironmentAwareness` |
  environment awareness is config capability
- MOVE `components/WorkerManager` -> `framework/System/Capabilities/WorkerManagement` | runtime lifecycle belongs to
  framework
- MOVE `components/ExternalState` -> `framework/System/Capabilities/ExternalState` | external state belongs to framework
- MOVE `components/StatelessBoundary` -> `framework/System/Capabilities/RuntimeSafety/StatelessBoundary` | runtime
  safety belongs to framework
- MOVE `components/GracefulShutdown` -> `framework/System/Capabilities/Runtime/GracefulShutdown` | runtime lifecycle
  belongs to framework
- MOVE `components/ResourceGovernor` -> `framework/System/Capabilities/ResourceGovernance` | resource governance belongs
  to framework
- REWRITE `components/Identity/Tokens/System/Capabilities/JwtAuth/System/Capabilities/Signing/JwtSigner.php`
- REWRITE `components/Identity/Tokens/System/Capabilities/JwtAuth/System/Capabilities/Verification/TokenVerifier.php`
- REWRITE `components/Identity/Tokens/System/Capabilities/JwtAuth/System/Capabilities/Tokens/JwtTokens.php`
- REWRITE `components/Identity/Tokens/System/Capabilities/JwtAuth/System/PublicSurface/JwtAuth.php`
- REWRITE `components/Identity/Access/System/Capabilities/Policy/System/Capabilities/Engine/PolicyEvaluator.php`
- REWRITE `components/Identity/Access/System/Capabilities/Policy/System/Capabilities/Rules/PolicyRule.php`
- REWRITE `components/Identity/Access/System/Capabilities/Policy/System/PublicSurface/Policy.php`
- REWRITE `components/Identity/Auth/examples/session-login.php`
- REWRITE `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ProvisionUser/ProvisionScimUser.php`
- REWRITE
  `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/RegisterDirectory/RegisterScimDirectory.php`
- REWRITE `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/RotateToken/RotateScimToken.php`
- REWRITE `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Directories/InMemoryScimDirectoryStore.php`
- REWRITE `components/Identity/Auth/System/Flows/Register/HashRegisteredPassword.php`
- REWRITE `components/Identity/Auth/System/Flows/ChangePassword/ChangePassword.php`
- REWRITE `components/Identity/Auth/System/Flows/RecoverAccess/PasswordReset/ResetPassword.php`
- REWRITE `components/Identity/Auth/System/Flows/ChangeEmail/BeginEmailChange.php`
- REWRITE `components/Identity/Auth/System/Flows/Login/VerifyPassword.php`
- REWRITE `components/Identity/Auth/System/Configuration/AuthBuilder.php`
- REWRITE `components/Identity/Auth/System/Configuration/RegisterAuthDependencies.php`
- REWRITE `components/Identity/Auth/System/Configuration/AuthRegistrar.php`
- REWRITE
  `components/Identity/ExternalIdentity/System/Capabilities/SingleSignOn/FederationRuntime/CompleteFederatedLogin/CompleteFederatedLogin.php`
- REWRITE `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Elements/InMemoryOAuthClientRegistry.php`
- REWRITE `components/Identity/Tenancy/System/Capabilities/Resolution/TenantResolver.php`
- REWRITE `components/Identity/Tenancy/System/Capabilities/Context/TenantContext.php`
- REWRITE `components/Identity/Tenancy/System/PublicSurface/Tenancy.php`
- REWRITE `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Backup/GenerateBackupCodes.php`
- REWRITE `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Backup/VerifyBackupCode.php`
- REWRITE `components/Security/System/Capabilities/SignedUrls/SignedUrlGenerator.php`
- REWRITE `components/Security/System/Capabilities/SignedUrls/SignedUrlVerifier.php`
- REWRITE `components/Security/System/Capabilities/Audit/SecurityAuditLog.php`
- REWRITE `components/Security/System/Capabilities/MassAssignment/MassAssignmentGuard.php`
- REWRITE `components/Security/System/Capabilities/Escape/OutputEscaper.php`
- REWRITE `components/Security/System/Capabilities/Csrf/CsrfToken.php`
- REWRITE `components/Security/System/Capabilities/Csrf/CsrfVerifier.php`
- REWRITE `components/Security/System/Capabilities/Headers/SecurityHeaders.php`
- REWRITE `components/Security/System/Hashing/System/Capabilities/PasswordHashing/PasswordHasher.php`
- REWRITE `components/Security/System/Secrets/System/Capabilities/Stores/SecretStore.php`
- REWRITE `components/Security/System/Secrets/System/Capabilities/Stores/InMemorySecretStore.php`
- REWRITE `components/Security/System/Secrets/System/Capabilities/Stores/EncryptedSecretStore.php`
- REWRITE `components/Security/System/Secrets/System/Capabilities/Encryption/SecretEncrypter.php`
- REWRITE `components/Security/System/Secrets/System/Flows/RedactSecret/RedactSecret.php`
- REWRITE `components/Security/System/Secrets/System/Flows/ReadSecret/ReadSecret.php`
- REWRITE `components/Security/System/Secrets/System/PublicSurface/Secrets.php`
- REWRITE `components/Security/System/PublicSurface/Security.php`
- REWRITE
  `components/DataStack/Database/System/Capabilities/QueryGovernance/System/Capabilities/Detection/NPlusOneDetector.php`
- REWRITE `components/DataStack/Database/System/Capabilities/QueryGovernance/System/PublicSurface/QueryGovernance.php`
- REWRITE
  `components/DeveloperTools/Diagnostics/System/Capabilities/ScalingReadiness/System/Capabilities/Checks/ScalingCheckResult.php`
- REWRITE
  `components/DeveloperTools/Diagnostics/System/Capabilities/ScalingReadiness/System/PublicSurface/ScalingReadiness.php`
- REWRITE `components/DeveloperTools/Diagnostics/System/Flows/ReadinessProbe/ReadinessProbe.php`
- REWRITE `components/DeveloperTools/Diagnostics/System/Flows/LivenessProbe/LivenessProbe.php`
- REWRITE `components/DeveloperTools/Diagnostics/System/PublicSurface/HealthCheck.php`
- REWRITE
  `components/DeveloperTools/Testing/System/Capabilities/ContractTesting/System/Capabilities/Verification/ContractVerifier.php`
- REWRITE
  `components/DeveloperTools/Testing/System/Capabilities/ContractTesting/System/PublicSurface/ContractTesting.php`
- REWRITE
  `components/Operations/ApplicationWorkflow/System/Capabilities/Orchestration/System/Capabilities/Kubernetes/Probes.php`
- REWRITE
  `components/Operations/ApplicationWorkflow/System/Capabilities/Orchestration/System/PublicSurface/Orchestration.php`
- REWRITE `components/Operations/Mail/System/Capabilities/Queue/MailQueue.php`
- REWRITE `components/Operations/Scheduler/System/Capabilities/TaskHistory/SchedulerHistory.php`
- REWRITE `components/Operations/Scheduler/System/Capabilities/Cron/CronExpression.php`
- REWRITE `components/Operations/Scheduler/System/Flows/RunDueTasks/RunDueTasks.php`
- REWRITE `components/Operations/Scheduler/System/Flows/RegisterScheduledTask/RegisterScheduledTask.php`
- REWRITE `components/Operations/Scheduler/System/PublicSurface/ScheduledTask.php`
- REWRITE `components/Operations/Scheduler/System/PublicSurface/SchedulerReport.php`
- REWRITE `components/Operations/Scheduler/System/PublicSurface/Scheduler.php`
- REWRITE `components/Operations/Scheduler/System/PublicSurface/TaskRunner.php`
- REWRITE `components/Operations/MessageBus/System/Capabilities/Middleware/BusMiddleware.php`
- REWRITE `components/Operations/MessageBus/System/Capabilities/Bus/MessageBusTraits.php`
- REWRITE `components/Operations/MessageBus/System/PublicSurface/MessageBus.php`
- REWRITE `components/Operations/Queue/System/Capabilities/TaskBus.php`
- REWRITE `components/Operations/Queue/System/Capabilities/Queue/Queue.php`
- REWRITE `components/Operations/Queue/System/Capabilities/Queue/RedisQueue.php`
- REWRITE
  `components/Operations/Queue/System/Capabilities/TaskDispatch/System/Capabilities/Resolution/DispatchStrategyResolver.php`
- REWRITE
  `components/Operations/Queue/System/Capabilities/TaskDispatch/System/Capabilities/Dispatchers/TaskDispatchers.php`
- REWRITE `components/Operations/Queue/System/Capabilities/TaskDispatch/System/PublicSurface/TaskDispatch.php`
- REWRITE `components/Operations/Queue/System/Foundation/JobInterface.php`
- REWRITE `components/Operations/Queue/System/PublicSurface/Tasks.php`
- REWRITE `components/Operations/Concurrency/System/Capabilities/EventLoopAdapters/SynchronousEventLoop.php`
- REWRITE `components/Operations/Concurrency/System/Capabilities/Cancellation/CancellationToken.php`
- REWRITE `components/Operations/Concurrency/System/Capabilities/Tasks/TaskRunner.php`
- REWRITE `components/Operations/Concurrency/System/Flows/RunConcurrentTasks/RunConcurrentTasks.php`
- REWRITE `components/Operations/Concurrency/System/Flows/AwaitTask/AwaitTask.php`
- REWRITE `components/Operations/Concurrency/System/PublicSurface/Concurrency.php`
- REWRITE `components/Operations/Realtime/System/Capabilities/WebSocket/WebSocketServer.php`
- REWRITE `components/Operations/Realtime/System/Capabilities/WebSocket/ChannelBroadcaster.php`
- REWRITE `components/Operations/Realtime/System/Capabilities/WebSocket/WebSocketClientScript.php`
- REWRITE `components/Operations/Realtime/System/Capabilities/WebSocket/UserBroadcaster.php`
- REWRITE `components/Operations/Realtime/System/Capabilities/WebSocket/PresenceChannel.php`
- REWRITE `components/Operations/Realtime/System/Capabilities/WebSocket/BroadcastMessage.php`
- REWRITE `components/Operations/Realtime/System/Capabilities/Channels/Channel.php`
- REWRITE `components/Operations/Realtime/System/Capabilities/Channels/Channels.php`
- REWRITE `components/Operations/Realtime/System/Capabilities/Connections/ConnectionPool.php`
- REWRITE `components/Operations/Realtime/System/Capabilities/Connections/Connection.php`
- REWRITE `components/Operations/Realtime/System/PublicSurface/Realtime.php`
- REWRITE `components/Operations/Resilience/System/Capabilities/Fallback/System/PublicSurface/Fallback.php`
- REWRITE `components/Operations/Resilience/System/Capabilities/Retry/RetryBuilder.php`
- REWRITE `components/Operations/Resilience/System/Capabilities/Retry/RetryOptions.php`
- REWRITE `components/Operations/Resilience/System/Capabilities/Retry/RetryResult.php`
- REWRITE `components/Operations/Resilience/System/Capabilities/Retry/RetryExecutor.php`
- REWRITE
  `components/Operations/Resilience/System/Capabilities/Idempotency/System/Capabilities/Keys/IdempotencyStore.php`
- REWRITE `components/Operations/Resilience/System/Capabilities/Idempotency/System/PublicSurface/Idempotency.php`
- REWRITE `components/Operations/Resilience/System/Capabilities/Backoff/BackoffSchedule.php`
- REWRITE `components/Operations/Resilience/System/Capabilities/RateLimiter/RedisRateLimiter.php`
- REWRITE `components/Operations/Resilience/System/Capabilities/RateLimiter/RateLimitMiddleware.php`
- REWRITE `components/Operations/Resilience/System/Capabilities/RateLimiter/RateLimiter.php`
- REWRITE `components/Operations/Resilience/System/Capabilities/RateLimiter/RateLimitDecision.php`
- REWRITE `components/Operations/Resilience/System/Capabilities/RateLimiter/RateLimit.php`
- REWRITE `components/Operations/Resilience/System/Capabilities/CircuitBreaker/CircuitBreakerState.php`
- REWRITE `components/Operations/Resilience/System/Capabilities/CircuitBreaker/CircuitBreaker.php`
- REWRITE `components/Operations/Resilience/System/Flows/RetryOperation/RetryOperation.php`
- REWRITE `components/Operations/Resilience/System/Flows/BreakCircuit/BreakCircuit.php`
- REWRITE `components/Operations/Resilience/System/PublicSurface/Resilience.php`
- REWRITE `components/HTTP/AfterResponse/System/Capabilities/Tasks/AfterResponseQueue.php`
- REWRITE `components/HTTP/AfterResponse/System/PublicSurface/AfterResponse.php`
- REWRITE `components/HTTP/ApiVersioning/System/PublicSurface/ApiVersion.php`
- REWRITE `components/HTTP/ContentNegotiation/System/Capabilities/Formats/ContentFormatters.php`
- REWRITE `components/HTTP/ContentNegotiation/System/Capabilities/Negotiator/AcceptHeaderParser.php`
- REWRITE `components/HTTP/ContentNegotiation/System/PublicSurface/ContentNegotiation.php`
- REWRITE `components/Application/Pipeline/System/Capabilities/Hooks/PipelineHook.php`
- REWRITE `components/Application/Pipeline/System/PublicSurface/Pipeline.php`
- REWRITE
  `components/Application/Config/System/Capabilities/EnvironmentAwareness/System/Capabilities/Detection/EnvironmentDetector.php`
- REWRITE
  `components/Application/Config/System/Capabilities/EnvironmentAwareness/System/Capabilities/Policies/EnvironmentPolicies.php`
- REWRITE `components/Application/Config/System/Capabilities/EnvironmentAwareness/System/PublicSurface/Environment.php`
- REWRITE `components/Application/FeatureFlags/System/Capabilities/Flags/InMemoryFlagStore.php`
- REWRITE `components/Application/FeatureFlags/System/PublicSurface/FeatureFlags.php`

## Conflicts

- none

## Notes

- Tests were not modified.
- Security is now a real suite in check-component-suite-structure.php.
- Runtime lifecycle moved to framework/System/Capabilities/.
- Run composer dump-autoload after apply.
- Run architecture checkers after apply.

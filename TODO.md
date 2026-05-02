Da. Treba ti **component completion plan**, ali prvo da presečemo jednu stvar: ne smeš da završiš komponente tako što će
agent samo napravi prazne `Configuration/`, `Flows/`, `Foundation/` foldere. To bi bilo arhitektonsko pozorište.
Komponenta je završena tek kada svaki folder ima **stvaran razlog, stvaran kod, test, i javnu granicu**.

Tvoj plan kaže da je kompletna komponenta ona koja ima `Capabilities`, `Configuration`, `Flows`, `Foundation`,
`PublicSurface`, uz testove, type-check, lint, dokumentaciju i `how-this-works.md`. To je dobar standard, ali mora se
primeniti bez praznih placeholdera.

Takođe, trenutni dump pokazuje da `Application/Cache` i dalje ima stari dokumentacioni/namespace miris:
`Foundation/Cache`, `Avax\Cache\Cache`, `Avax\Cache\System`, `Avax\Container\Providers\ServiceProvider`, i
component-local `tests/`. To znači da “complete” ne sme značiti samo “ima foldere”, nego i “nema staru istinu u kodu i
dokumentaciji”.

## Glavni redosled

Radi u četiri velika prolaza:

```text
1. Taxonomy Integrity Pass
2. Component Structural Completion Pass
3. Component Behavior Completion Pass
4. Proof Pass: tests, static analysis, docs, final matrix
```

Ne kreći odmah da “kompletiraš” 30 komponenti. Prvo stabilizuj pravila i napravi matricu.

---

# 1. Taxonomy Integrity Pass

Ovo mora biti P0, jer ako folderi još nisu na pravom mestu, komponentna kompletacija će samo cementirati pogrešnu
arhitekturu.

AI zadatak:

```text
You are doing AvaX Taxonomy Integrity Pass.

Do not add features.
Do not complete components yet.
Do not repair tests yet.
Do not create placeholder folders.

Goal:
Make sure every component is in the correct suite and no old taxonomy artifacts remain.

Tasks:
1. Run:
   find components -type d -path '*System/Capabilities/*/System*' | sort
   find components -type d -path '*System/Foundation/*/System*' | sort
   find components -type d -path '*System/PublicSurface/*/System*' | sort

2. Fix every nested System folder.

3. Verify Security shape:
   components/Security/Hashing/System
   components/Security/Secrets/System
   components/Security/Cryptography/System
   components/Security/Redaction/System
   components/Security/Audit/System

   Do not keep:
   components/Security/System/Hashing
   components/Security/System/Secrets

4. Verify Application/Cache has no stale namespace:
   grep -R "namespace Avax\\\\Cache" -n components/Application/Cache || true
   grep -R "use Avax\\\\Cache" -n components/Application/Cache || true
   grep -R "use Avax\\\\Container" -n components/Application/Cache || true
   grep -R "Foundation/Cache" -n components/Application/Cache || true

5. Verify no top-level extra components remain outside final suites.

6. Run:
   composer dump-autoload -o
   php tooling/refactor/check-component-suite-structure.php
   php tooling/refactor/check-duplicate-owners.php
   php tooling/refactor/check-namespace-drift.php

Acceptance:
- no nested System folders
- Security suite shape correct
- Cache old namespace/docs cleaned
- composer autoload passes
- structure/duplicate/namespace checkers pass
```

Ako ovo nije zeleno, ne prelazi dalje.

---

# 2. Component Structural Completion Pass

Ovo je faza gde se dodaju nedostajući folderi, ali **samo sa smislenim minimalnim sadržajem**.

## Pravilo za svaki folder

```text
Capabilities/
  Reusable mechanism or feature behavior.
  No orchestration-heavy code.

Configuration/
  Register<Component>Dependencies.php
  <Component>Configuration.php
  Build<Component>.php if builder is useful.

Flows/
  One obvious use-case / operation owner.
  Example: ResolveFacadeRoot, EvaluateFeatureFlag, RunPipeline.

Foundation/
  Local contracts, value objects, failures.
  No generic global Contracts/Exceptions dumping.

PublicSurface/
  Stable public API only.
  Facade, public contract, root entrypoint, stable DTO/VO if part of API.
```

Minimalna “kompletna” komponenta ne znači mnogo koda. Znači **jasna granica**.

---

# 3. Prioriteti po komponentama

## Phase A: HIGH, prvo ovo

Tvoj upload identifikuje kao high priority: `Application/Facade`, `Application/FeatureFlags`, `Application/Pipeline`, i
`CLI/Commands`; `CLI/UI` je medium, ali bih ga radio odmah uz CLI jer pripada istom operativnom sloju.

### A1. `Application/Facade`

Target:

```text
components/Application/Facade/System/
  PublicSurface/
    Facade.php
    FacadeRoot.php
    FacadeRootNotFound.php

  Capabilities/
    ResolveFacadeRoot/
      ResolveFacadeRoot.php
      FacadeRootRegistry.php

    ManageResolvedFacades/
      RememberResolvedFacade.php
      ClearResolvedFacades.php

  Configuration/
    RegisterFacadeDependencies.php
    FacadeConfiguration.php

  Flows/
    CallFacadeMethod/
      CallFacadeMethod.php

    ResetFacadeState/
      ResetFacadeState.php

  Foundation/
    Failure/
      FacadeFailure.php
      FacadeRootMissing.php

    Values/
      FacadeAccessor.php
```

Acceptance:

```text
[ ] Facade has reset hook for long-lived workers.
[ ] No actual DB/Cache/Auth/Route facades live here.
[ ] Actual facades live in owning components.
[ ] PublicSurface contains only stable API.
[ ] Tests cover resolve, call, missing root, reset.
```

### A2. `Application/FeatureFlags`

Target:

```text
components/Application/FeatureFlags/System/
  PublicSurface/
    FeatureFlags.php
    FeatureFlag.php

  Capabilities/
    StoreFeatureFlags/
      FeatureFlagStore.php
      InMemoryFeatureFlagStore.php

    EvaluateFeatureFlags/
      EvaluateFeatureFlag.php
      FeatureFlagDecision.php

    TargetFeatureFlags/
      FeatureTarget.php
      TargetingRule.php

    RolloutFeatureFlags/
      PercentageRollout.php

  Configuration/
    FeatureFlagsConfiguration.php
    RegisterFeatureFlagDependencies.php

  Flows/
    CheckFeatureFlag/
      CheckFeatureFlag.php

    EnableFeatureFlag/
      EnableFeatureFlag.php

    DisableFeatureFlag/
      DisableFeatureFlag.php

  Foundation/
    Values/
      FeatureFlagName.php

    Failure/
      FeatureFlagNotFound.php
```

Acceptance:

```text
[ ] Can enable/disable flag.
[ ] Can evaluate by name.
[ ] Can evaluate by target/context.
[ ] Has default/fallback behavior.
[ ] No global static state without reset.
```

### A3. `Application/Pipeline`

Target:

```text
components/Application/Pipeline/System/
  PublicSurface/
    Pipeline.php
    Pipe.php

  Capabilities/
    BuildPipeline/
      PipelineBuilder.php

    ExecutePipeline/
      PipelineRunner.php
      PipelineStage.php

    HandlePipelineFailure/
      PipelineFailureHandler.php

  Configuration/
    PipelineConfiguration.php
    RegisterPipelineDependencies.php

  Flows/
    RunPipeline/
      RunPipeline.php

  Foundation/
    Failure/
      PipelineFailed.php

    Values/
      PipelineResult.php
```

Acceptance:

```text
[ ] Runs stages in order.
[ ] Supports short-circuit if designed.
[ ] Handles failure deterministically.
[ ] Does not become generic magic bus.
```

### A4. CLI decision: merge `CLI/Commands` and `CLI/UI` into `CLI/Console`

Ovde bih bio oštar. Nemoj držati `CLI/Commands` i `CLI/UI` kao posebne komponente ako već imaš `CLI/Console`. To pravi
tri vlasnika za isti CLI runtime.

Predlog:

```text
components/CLI/Console/System/
  PublicSurface/
    Console.php
    Command.php

  Capabilities/
    CommandRegistry/
    Input/
    Output/
    Arguments/
    Options/
    UI/
      Table.php
      ProgressBar.php
      Question.php
      Confirm.php

  Configuration/
    ConsoleConfiguration.php
    RegisterConsoleDependencies.php

  Flows/
    RunConsoleCommand/
    ResolveConsoleCommand/
    ParseConsoleInput/
    RenderConsoleOutput/

  Foundation/
    Values/
      ExitCode.php
      CommandName.php

    Failure/
      CommandNotFound.php
      CommandFailed.php
```

Acceptance:

```text
[ ] CLI/Commands is removed or bridge-only.
[ ] CLI/UI is removed or folded into CLI/Console/System/Capabilities/UI.
[ ] One CLI runtime owner exists: CLI/Console.
```

---

## Phase B: MEDIUM, HTTP completion

Tvoj plan označava kao incomplete: `HTTP/AfterResponse`, `HTTP/ApiVersioning`, `HTTP/ContentNegotiation`,
`HTTP/Context`, `HTTP/Dispatcher`, `HTTP/Security`.

### B1. `HTTP/AfterResponse`

```text
Configuration/
  AfterResponseConfiguration.php
  RegisterAfterResponseDependencies.php

Foundation/
  Values/
    AfterResponseTaskId.php
  Failure/
    AfterResponseTaskFailed.php

Flows/
  RunAfterResponseTasks/
  RegisterAfterResponseTask/
```

Capabilities već postoje, ali proveri da nema samo queue skeleton.

Acceptance:

```text
[ ] Can register task.
[ ] Can run tasks after response emission.
[ ] Failure policy is explicit.
[ ] Does not block response path unless configured.
```

### B2. `HTTP/ApiVersioning`

```text
Configuration/
  ApiVersioningConfiguration.php

Foundation/
  Values/
    ApiVersion.php
    VersionedRoute.php

Flows/
  ResolveApiVersion/
  RejectUnsupportedApiVersion/
```

Acceptance:

```text
[ ] Resolves version from header/path/query if configured.
[ ] Rejects unsupported version.
[ ] Integrates with Router without Router owning version logic.
```

### B3. `HTTP/ContentNegotiation`

```text
Configuration/
  ContentNegotiationConfiguration.php

Foundation/
  Values/
    MediaType.php
    AcceptHeader.php

Flows/
  NegotiateContent/
  FormatResponseContent/
```

Acceptance:

```text
[ ] Parses Accept header.
[ ] Chooses best formatter.
[ ] Has fallback behavior.
```

### B4. `HTTP/Context`

```text
Configuration/
  HttpContextConfiguration.php
  RegisterHttpContextDependencies.php

Foundation/
  Values/
    ServerVariables.php

Flows/
  ReadHttpContext/
```

Acceptance:

```text
[ ] Wraps PHP globals testably.
[ ] No direct global access outside this component/request factory.
```

### B5. `HTTP/Dispatcher`

```text
Configuration/
  DispatcherConfiguration.php

Foundation/
  Failure/
    DispatchFailed.php

Flows/
  DispatchRoute/
  DispatchController/
```

Acceptance:

```text
[ ] Dispatcher coordinates route handler execution.
[ ] Router does matching, Dispatcher executes.
```

### B6. `HTTP/Security`

```text
Configuration/
  HttpSecurityConfiguration.php
  RegisterHttpSecurityDependencies.php

Foundation/
  Values/
    CsrfToken.php
    SignedUrlSignature.php

Capabilities/
  Csrf/
  Headers/
  SignedUrls/
  TrustedProxy/
  TrustedHost/

Flows/
  VerifyCsrfToken/
  ApplySecurityHeaders/
  VerifySignedUrl/
```

Acceptance:

```text
[ ] HTTP security stays HTTP-specific.
[ ] Encryption/hashing/secrets do not live here.
```

---

## Phase C: MEDIUM, Identity completion

Tvoj plan navodi `Identity/Credentials`, `Identity/ExternalIdentity`, `Identity/Tenancy` kao incomplete.

### C1. `Identity/Credentials`

```text
PublicSurface/
  Credentials.php

Configuration/
  CredentialsConfiguration.php
  RegisterCredentialDependencies.php

Flows/
  VerifyPassword/
  ChangePassword/
  GenerateMfaChallenge/
  VerifyMfaChallenge/
  GenerateRecoveryCodes/
  VerifyRecoveryCode/

Foundation/
  Values/
    PasswordHash.php
    RecoveryCode.php
  Failure/
    InvalidCredential.php
```

Acceptance:

```text
[ ] Password/MFA/passkey/recovery code behavior lives here.
[ ] Auth calls Credentials, Auth does not own credential mechanics.
```

### C2. `Identity/ExternalIdentity`

```text
PublicSurface/
  ExternalIdentity.php

Configuration/
  ExternalIdentityConfiguration.php

Flows/
  StartExternalLogin/
  CompleteExternalLogin/
  LinkExternalIdentity/
  UnlinkExternalIdentity/

Foundation/
  Values/
    ProviderName.php
    ExternalSubjectId.php
```

Acceptance:

```text
[ ] OAuth/OIDC/SSO/Federation live here.
[ ] Auth only consumes result.
```

### C3. `Identity/Tenancy`

```text
Configuration/
  TenancyConfiguration.php
  RegisterTenancyDependencies.php

Flows/
  ResolveTenant/
  SwitchTenant/
  InviteTenantMember/
  TransferTenantOwnership/

Foundation/
  Values/
    TenantId.php
    TenantSlug.php
  Failure/
    TenantNotFound.php
```

Acceptance:

```text
[ ] Tenant context is request-safe.
[ ] No static tenant state leaks across workers.
```

---

## Phase D: MEDIUM, Operations completion

Tvoj plan navodi `ApplicationWorkflow`, `Concurrency`, `MessageBus`, `Monitoring`, `Realtime`, `Resilience`,
`Scheduler`, plus `Mail` missing Foundation.

### D1. `Operations/ApplicationWorkflow`

Dodaj:

```text
Configuration/
  WorkflowConfiguration.php

Foundation/
  Values/
    WorkflowId.php
    SagaId.php
  Failure/
    WorkflowFailed.php
    SagaCompensationFailed.php
```

Acceptance:

```text
[ ] Saga state has explicit value objects.
[ ] Compensation failure is modeled.
[ ] Workflow events integrate with Operations/Events.
```

### D2. `Operations/Concurrency`

```text
Configuration/
  ConcurrencyConfiguration.php

Foundation/
  Contracts/
    EventLoop.php
    CancellationToken.php
  Failure/
    TaskCancelled.php
    ConcurrentTaskFailed.php
```

Ovde “Contracts” kao folder je inače zabranjen kod tebe, pa bolje:

```text
Foundation/
  EventLoop.php
  CancellationToken.php
  Failure/
```

Acceptance:

```text
[ ] Synchronous adapter exists.
[ ] Async runtime adapters do not leak into core.
```

### D3. `Operations/MessageBus`

```text
Configuration/
  MessageBusConfiguration.php
  RegisterMessageBusDependencies.php

Flows/
  DispatchMessage/
  HandleMessage/

Foundation/
  Values/
    MessageId.php
  Failure/
    MessageDispatchFailed.php
```

Acceptance:

```text
[ ] MessageBus is not Queue.
[ ] MessageBus dispatches messages.
[ ] Queue handles background persistence/workers.
```

### D4. `Operations/Monitoring` vs `Operations/Observability`

Ne bih držao oba. Izaberi jedno. Ja bih koristio:

```text
Operations/Observability
```

Ako postoji `Operations/Monitoring`, premesti ili bridge-uj u `Operations/Observability`.

Target:

```text
Operations/Observability/System/
  PublicSurface/
    Observability.php

  Capabilities/
    Metrics/
    Tracing/
    Timeline/
    Health/

  Configuration/
    ObservabilityConfiguration.php

  Flows/
    RecordMetric/
    StartTrace/
    RecordTimelineEvent/

  Foundation/
    Values/
      TraceId.php
      MetricName.php
```

Acceptance:

```text
[ ] Monitoring is removed or bridge-only.
[ ] Observability owns metrics/tracing/timeline.
```

### D5. `Operations/Realtime`

```text
Configuration/
  RealtimeConfiguration.php

Flows/
  OpenRealtimeConnection/
  BroadcastRealtimeMessage/
  JoinRealtimeChannel/
  LeaveRealtimeChannel/

Foundation/
  Values/
    ChannelName.php
    ConnectionId.php
  Failure/
    RealtimeConnectionFailed.php
```

Acceptance:

```text
[ ] WebSocket specifics are adapter-bound.
[ ] Realtime core does not depend on Swoole/Workerman APIs directly.
```

### D6. `Operations/Resilience`

```text
Configuration/
  ResilienceConfiguration.php

Foundation/
  Values/
    RetryAttempt.php
    TimeoutDuration.php
  Failure/
    CircuitOpen.php
    RetryExhausted.php
```

Acceptance:

```text
[ ] Retry, timeout, circuit breaker, rate limiter have shared policy model.
[ ] Idempotency/Fallback live here if generic.
```

### D7. `Operations/Scheduler`

```text
Configuration/
  SchedulerConfiguration.php

Foundation/
  Values/
    ScheduleId.php
    CronExpression.php
  Failure/
    ScheduledTaskFailed.php
```

Acceptance:

```text
[ ] Scheduler registers tasks.
[ ] Scheduler runs due tasks.
[ ] Task history is modeled.
```

### D8. `Operations/Mail`

Only missing `Foundation`, so this is small:

```text
Foundation/
  Values/
    EmailAddress.php
    MailSubject.php
  Failure/
    MailSendFailed.php
    MailTransportUnavailable.php
```

---

## Phase E: DeveloperTools completion

### E1. `DeveloperTools/CodeGeneration`

```text
PublicSurface/
  CodeGeneration.php

Configuration/
  CodeGenerationConfiguration.php

Flows/
  GenerateClass/
  GenerateController/
  GenerateEntity/
  GenerateRepository/
  GenerateService/

Foundation/
  Values/
    GeneratedFilePath.php
    StubName.php
  Failure/
    CodeGenerationFailed.php
```

Acceptance:

```text
[ ] Generator logic is here.
[ ] CLI only calls it.
```

### E2. `DeveloperTools/DumpDebugger`

```text
Capabilities/
  ErrorScreens/
  SourcePreview/
  Redaction/
  Dumps/

Configuration/
  DumpDebuggerConfiguration.php

Flows/
  RenderDebugError/
  RenderCliDebugError/

Foundation/
  Failure/
    DebugRenderFailed.php
```

Acceptance:

```text
[ ] Disabled in production by config.
[ ] Redacts secrets.
```

### E3. `DeveloperTools/Testing`

```text
PublicSurface/
  Testing.php

Configuration/
  TestingConfiguration.php

Flows/
  FakeComponent/
  ResetFakes/

Foundation/
  Failure/
    FakeNotRegistered.php
```

Acceptance:

```text
[ ] EventFake, CacheFake, MailFake, QueueFake, HttpFake have one owner.
[ ] Fakes reset between tests.
```

---

## Phase F: Security completion

Tvoj plan navodi `Security`, `Security/Hashing`, `Security/Secrets`; samo pazi, `Security` mora biti suite, ne component
ako si usvojio finalni model.

### F1. `Security/Hashing`

```text
PublicSurface/
  Hashing.php
  PasswordHasher.php

Configuration/
  HashingConfiguration.php

Flows/
  HashPassword/
  VerifyPasswordHash/

Foundation/
  Values/
    PasswordHash.php
  Failure/
    HashingFailed.php
```

### F2. `Security/Secrets`

```text
Configuration/
  SecretsConfiguration.php

Foundation/
  Values/
    SecretName.php
    SecretValue.php
  Failure/
    SecretNotFound.php
    SecretDecryptionFailed.php
```

### F3. `Security/Cryptography`, ako ne postoji

```text
PublicSurface/
  Cryptography.php
  Encryption.php

Capabilities/
  Encryption/
  Signing/
  Keys/

Configuration/
  CryptographyConfiguration.php

Flows/
  EncryptValue/
  DecryptValue/
  SignPayload/
  VerifySignature/

Foundation/
  Values/
    EncryptionKey.php
    EncryptedPayload.php
  Failure/
    EncryptionFailed.php
    SignatureVerificationFailed.php
```

---

# 4. Šta NE treba raditi

Ovo stavi agentu boldovano:

```text
Do not create empty folders only to satisfy structure.
Do not add placeholder classes with describeResponsibility().
Do not create Contracts/Exceptions generic buckets.
Do not create Service/Manager/Helper/Util names.
Do not add tests inside components/*/tests.
Do not mix test repair with component completion.
Do not change domain behavior just to make the structure look complete.
Do not create duplicate owners such as CLI/Commands and CLI/Console both owning command runtime.
Do not keep Monitoring and Observability as two owners for the same concept.
Do not keep Security/System/Hashing. Security is a suite, Hashing is a component.
```

---

# 5. Exact AI prompt

Ovo možeš direktno da pošalješ Codex-u:

```text
You are working on AvaX Component Completion.

Read CURRENT_TRUTH.md first.

This pass starts only after Component Taxonomy Integrity is GREEN.

Goal:
Complete incomplete AvaX components by adding the missing architectural lanes with real, minimal, meaningful implementation.

Do not create empty decorative folders.
Do not create placeholder classes.
Do not add describeResponsibility-only classes.
Do not repair tests in this pass unless the component code is completed and the test is strictly local to that component.
Do not change domain behavior unless needed to wire the component correctly.
Do not add new features outside the listed component completion scope.

Definition of a completed component:
- Correct suite and component owner
- Canonical namespace
- Meaningful Capabilities
- Meaningful Configuration
- Meaningful Flows
- Meaningful Foundation
- Thin PublicSurface
- No duplicate owner
- No stale old namespace
- No component-local tests
- how-this-works.md updated
- unit tests planned or added in root tests/
- architecture checkers pass

Phase A HIGH:
1. Complete Application/Facade.
2. Complete Application/FeatureFlags.
3. Complete Application/Pipeline.
4. Normalize CLI:
   - CLI/Console is the single command runtime owner.
   - Fold CLI/Commands and CLI/UI into CLI/Console or make them bridge-only.

Phase B HTTP:
5. Complete HTTP/AfterResponse.
6. Complete HTTP/ApiVersioning.
7. Complete HTTP/ContentNegotiation.
8. Complete HTTP/Context.
9. Complete HTTP/Dispatcher.
10. Complete HTTP/Security.

Phase C Identity:
11. Complete Identity/Credentials.
12. Complete Identity/ExternalIdentity.
13. Complete Identity/Tenancy.

Phase D Operations:
14. Complete Operations/ApplicationWorkflow.
15. Complete Operations/Concurrency.
16. Complete Operations/MessageBus.
17. Normalize Monitoring into Operations/Observability.
18. Complete Operations/Realtime.
19. Complete Operations/Resilience.
20. Complete Operations/Scheduler.
21. Add missing Foundation to Operations/Mail.

Phase E DeveloperTools:
22. Complete DeveloperTools/CodeGeneration.
23. Complete DeveloperTools/DumpDebugger.
24. Complete DeveloperTools/Testing.

Phase F Security:
25. Complete Security/Hashing.
26. Complete Security/Secrets.
27. Create or complete Security/Cryptography if encryption/signing/key code exists.

For each component:
1. Inspect existing files.
2. Identify missing lanes.
3. Move existing code into the correct lane first.
4. Add only minimal missing classes needed for a real component.
5. Update namespace/imports.
6. Update how-this-works.md.
7. Do not touch unrelated components.
8. Run:
   composer dump-autoload -o
   php tooling/refactor/check-component-suite-structure.php
   php tooling/refactor/check-duplicate-owners.php
   php tooling/refactor/check-namespace-drift.php

Create:
Code-Review-And-ToDo/component-completion/component-completion-matrix.md

Matrix columns:
- Component
- Current missing lanes
- Added lanes
- Real implementation added
- PublicSurface status
- Tests status
- Docs status
- Remaining risk
- Status: GREEN/YELLOW/RED

Do not mark a component GREEN if it only has folders.
```

---

# 6. Finalni redosled rada

Najkraće:

```text
0. Taxonomy Integrity GREEN
1. Application/Facade
2. Application/FeatureFlags
3. Application/Pipeline
4. CLI/Console normalization
5. HTTP incomplete components
6. Identity incomplete components
7. Operations incomplete components
8. DeveloperTools incomplete components
9. Security incomplete components
10. Minor: Application/Text Configuration, Operations/Mail Foundation
11. component-completion-matrix.md
12. architecture checkers
13. tek onda Test Layer Repair
```

Moj hladan savet: ne dozvoli agentu da “kompletira” sve komponente u jednom ogromnom diff-u. Nek radi **po fazama**,
najviše 3–5 komponenti po pass-u. Inače ćeš dobiti 200 novih fajlova i opet nećeš znati šta je stvarno završeno, a šta
je samo lepo upakovano.

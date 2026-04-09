<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;

$config = CreateContainerConfig::create(
    cacheDir: '/tmp/container-cache',
    debug: true,
    settings: ['app' => ['name' => 'Container'], 'app_env' => 'prod', 'benchmark' => ['build_marker' => 'ci-smoke']],
    strict: true,
    compileMode: CreateContainerConfig::COMPILE_MODE_CI,
    diagnosticsMode: CreateContainerConfig::DIAGNOSTICS_MODE_CI,
    executionMode: CreateContainerConfig::EXECUTION_MODE_GENERATED,
    pruneMode: CreateContainerConfig::PRUNE_MODE_STRICT,
    policyProfile: CreateContainerConfig::POLICY_PROFILE_RELAXED,
    policyFailMode: CreateContainerConfig::POLICY_FAIL_MODE_OPEN,
    policyProfiles: ['prod' => CreateContainerConfig::POLICY_PROFILE_STRICT],
    sliceBoundaryMode: CreateContainerConfig::SLICE_BOUNDARY_MODE_PROJECTED,
    asyncTarget: CreateContainerConfig::ASYNC_TARGET_WORKER
);

$changed = $config
    ->withCacheDir('/tmp/other-cache')
    ->withDebug(false)
    ->withStrict(false)
    ->withCompileMode(CreateContainerConfig::COMPILE_MODE_DEV)
    ->withDiagnosticsMode(CreateContainerConfig::DIAGNOSTICS_MODE_MINIMAL)
    ->withExecutionMode(CreateContainerConfig::EXECUTION_MODE_DYNAMIC)
    ->withPruneMode(CreateContainerConfig::PRUNE_MODE_NONE)
    ->withPolicyProfile(CreateContainerConfig::POLICY_PROFILE_RELAXED)
    ->withPolicyFailMode(CreateContainerConfig::POLICY_FAIL_MODE_CLOSED)
    ->withPolicyProfiles(['dev' => CreateContainerConfig::POLICY_PROFILE_RELAXED])
    ->withSliceBoundaryMode(CreateContainerConfig::SLICE_BOUNDARY_MODE_STRICT)
    ->withAsyncTarget(CreateContainerConfig::ASYNC_TARGET_FPM)
    ->withSettings(['app' => ['name' => 'Other']]);

assertSame('/tmp/container-cache', $config->cacheDir, 'Original config must stay immutable.');
assertTrue($config->debug, 'Original debug flag must stay unchanged.');
assertTrue($config->strict, 'Original strict flag must stay unchanged.');
assertSame(CreateContainerConfig::COMPILE_MODE_CI, $config->compileMode, 'Original compile mode must stay unchanged.');
assertSame(CreateContainerConfig::DIAGNOSTICS_MODE_CI, $config->diagnosticsMode, 'Original diagnostics mode must stay unchanged.');
assertSame(CreateContainerConfig::EXECUTION_MODE_GENERATED, $config->executionMode, 'Original execution mode must stay unchanged.');
assertSame(CreateContainerConfig::PRUNE_MODE_STRICT, $config->pruneMode, 'Original prune mode must stay unchanged.');
assertSame(CreateContainerConfig::POLICY_PROFILE_RELAXED, $config->policyProfile, 'Original policy profile must stay unchanged.');
assertSame(CreateContainerConfig::POLICY_FAIL_MODE_OPEN, $config->policyFailMode, 'Original policy fail mode must stay unchanged.');
assertSame(['prod' => CreateContainerConfig::POLICY_PROFILE_STRICT], $config->policyProfiles, 'Original policy profile map must stay unchanged.');
assertSame(CreateContainerConfig::SLICE_BOUNDARY_MODE_PROJECTED, $config->sliceBoundaryMode, 'Original slice boundary mode must stay unchanged.');
assertSame(CreateContainerConfig::ASYNC_TARGET_WORKER, $config->asyncTarget, 'Original async target must stay unchanged.');
assertSame('Container', $config->settings['app']['name'], 'Original settings must stay unchanged.');
assertSame('/tmp/other-cache', $changed->cacheDir, 'Changed config should carry the new cache dir.');
assertTrue(! $changed->debug, 'Changed config should carry the new debug flag.');
assertTrue(! $changed->strict, 'Changed config should carry the new strict flag.');
assertSame(CreateContainerConfig::COMPILE_MODE_DEV, $changed->compileMode, 'Changed config should carry the new compile mode.');
assertSame(CreateContainerConfig::DIAGNOSTICS_MODE_MINIMAL, $changed->diagnosticsMode, 'Changed config should carry the new diagnostics mode.');
assertSame(CreateContainerConfig::EXECUTION_MODE_DYNAMIC, $changed->executionMode, 'Changed config should carry the new execution mode.');
assertSame(CreateContainerConfig::PRUNE_MODE_NONE, $changed->pruneMode, 'Changed config should carry the new prune mode.');
assertSame(CreateContainerConfig::POLICY_PROFILE_RELAXED, $changed->policyProfile, 'Changed config should carry the new policy profile.');
assertSame(CreateContainerConfig::POLICY_FAIL_MODE_CLOSED, $changed->policyFailMode, 'Changed config should carry the new policy fail mode.');
assertSame(['dev' => CreateContainerConfig::POLICY_PROFILE_RELAXED], $changed->policyProfiles, 'Changed config should carry the new policy profile map.');
assertSame(CreateContainerConfig::SLICE_BOUNDARY_MODE_STRICT, $changed->sliceBoundaryMode, 'Changed config should carry the new slice boundary mode.');
assertSame(CreateContainerConfig::ASYNC_TARGET_FPM, $changed->asyncTarget, 'Changed config should carry the new async target.');
assertSame('Other', $changed->settings['app']['name'], 'Changed config should carry the new settings.');
assertTrue($config->validatesBeforeCompile(), 'CI mode should validate before compile.');
assertTrue(! $changed->validatesCompiledArtifactsOnLoad(), 'Dynamic mode should skip compiled artifact validation on load because the hot path stays disabled.');
assertTrue($config->validatesCompiledArtifactsOnLoad(), 'Generated execution should still validate freshness and compatibility before the hot path attaches.');
assertTrue($config->failsClosedOnCompiledCorruption(), 'Strict config should fail closed on compiled corruption.');
assertTrue($config->usesDetailedDiagnostics(), 'CI diagnostics mode should opt into high-detail observability.');
assertTrue(! $changed->usesDetailedDiagnostics(), 'Minimal diagnostics mode without debug should disable high-detail observability.');
assertTrue($config->usesGeneratedExecution(), 'Generated execution mode helper should stay accurate.');
assertTrue(! $config->usesDynamicExecution(), 'Generated execution mode should not report as dynamic.');
assertTrue($changed->usesDynamicExecution(), 'Dynamic execution mode helper should stay accurate.');
assertTrue($config->usesStrictPruning(), 'Strict pruning helper should stay accurate.');
assertTrue(! $changed->usesStrictPruning(), 'Disabled pruning helper should stay accurate.');
assertTrue($config->supportsAsyncTarget(), 'Worker async target should be supported.');
assertSame(CreateContainerConfig::POLICY_PROFILE_STRICT, $config->effectivePolicyProfile(), 'Environment policy overrides should win when they match the active environment.');
assertTrue(! $config->failsClosedOnPolicy(), 'Open policy fail mode should stay explicit.');
assertTrue(! $config->usesStrictSliceBoundaries(), 'Projected slice boundary mode should stay explicit.');
assertTrue($changed->failsClosedOnPolicy(), 'Closed policy fail mode helper should stay accurate.');
assertTrue($changed->usesStrictSliceBoundaries(), 'Strict slice boundary helper should stay accurate.');
assertTrue($config->configHash() !== '', 'Config hash should be available for compile invalidation.');
assertTrue($config->settingsFingerprint() !== '', 'Settings fingerprint should be available for metadata provenance.');
assertSame('ci-smoke', $config->benchmarkBuildMarker(), 'Benchmark build marker should be available for benchmark artifacts and compile metadata.');

echo basename(__FILE__) . " ok\n";

<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, levels: 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;

$config = CreateContainerConfig::create(
    cacheDir         : '/tmp/container-cache',
    debug            : true,
    settings         : ['app' => ['name' => 'Container'], 'app_env' => 'prod', 'benchmark' => ['build_marker' => 'ci-smoke']],
    strict           : true,
    compileMode      : CreateContainerConfig::COMPILE_MODE_CI,
    diagnosticsMode  : CreateContainerConfig::DIAGNOSTICS_MODE_CI,
    executionMode    : CreateContainerConfig::EXECUTION_MODE_GENERATED,
    pruneMode        : CreateContainerConfig::PRUNE_MODE_STRICT,
    policyProfile    : CreateContainerConfig::POLICY_PROFILE_RELAXED,
    policyFailMode   : CreateContainerConfig::POLICY_FAIL_MODE_OPEN,
    policyProfiles   : ['prod' => CreateContainerConfig::POLICY_PROFILE_STRICT],
    sliceBoundaryMode: CreateContainerConfig::SLICE_BOUNDARY_MODE_PROJECTED,
    asyncTarget      : CreateContainerConfig::ASYNC_TARGET_WORKER
);

$changed = $config
    ->withCacheDir(cacheDir: '/tmp/other-cache')
    ->withDebug(debug: false)
    ->withStrict(strict: false)
    ->withCompileMode(compileMode: CreateContainerConfig::COMPILE_MODE_DEV)
    ->withDiagnosticsMode(diagnosticsMode: CreateContainerConfig::DIAGNOSTICS_MODE_MINIMAL)
    ->withExecutionMode(executionMode: CreateContainerConfig::EXECUTION_MODE_DYNAMIC)
    ->withPruneMode(pruneMode: CreateContainerConfig::PRUNE_MODE_NONE)
    ->withPolicyProfile(policyProfile: CreateContainerConfig::POLICY_PROFILE_RELAXED)
    ->withPolicyFailMode(policyFailMode: CreateContainerConfig::POLICY_FAIL_MODE_CLOSED)
    ->withPolicyProfiles(policyProfiles: ['dev' => CreateContainerConfig::POLICY_PROFILE_RELAXED])
    ->withSliceBoundaryMode(sliceBoundaryMode: CreateContainerConfig::SLICE_BOUNDARY_MODE_STRICT)
    ->withAsyncTarget(asyncTarget: CreateContainerConfig::ASYNC_TARGET_FPM)
    ->withSettings(settings: ['app' => ['name' => 'Other']]);

assertSame(expected: '/tmp/container-cache', actual: $config->cacheDir, message: 'Original config must stay immutable.');
assertTrue(condition: $config->debug, message: 'Original debug flag must stay unchanged.');
assertTrue(condition: $config->strict, message: 'Original strict flag must stay unchanged.');
assertSame(expected: CreateContainerConfig::COMPILE_MODE_CI, actual: $config->compileMode, message: 'Original compile mode must stay unchanged.');
assertSame(expected: CreateContainerConfig::DIAGNOSTICS_MODE_CI, actual: $config->diagnosticsMode, message: 'Original diagnostics mode must stay unchanged.');
assertSame(expected: CreateContainerConfig::EXECUTION_MODE_GENERATED, actual: $config->executionMode, message: 'Original execution mode must stay unchanged.');
assertSame(expected: CreateContainerConfig::PRUNE_MODE_STRICT, actual: $config->pruneMode, message: 'Original prune mode must stay unchanged.');
assertSame(expected: CreateContainerConfig::POLICY_PROFILE_RELAXED, actual: $config->policyProfile, message: 'Original policy profile must stay unchanged.');
assertSame(expected: CreateContainerConfig::POLICY_FAIL_MODE_OPEN, actual: $config->policyFailMode, message: 'Original policy fail mode must stay unchanged.');
assertSame(expected: ['prod' => CreateContainerConfig::POLICY_PROFILE_STRICT], actual: $config->policyProfiles, message: 'Original policy profile map must stay unchanged.');
assertSame(expected: CreateContainerConfig::SLICE_BOUNDARY_MODE_PROJECTED, actual: $config->sliceBoundaryMode, message: 'Original slice boundary mode must stay unchanged.');
assertSame(expected: CreateContainerConfig::ASYNC_TARGET_WORKER, actual: $config->asyncTarget, message: 'Original async target must stay unchanged.');
assertSame(expected: 'Container', actual: $config->settings['app']['name'], message: 'Original settings must stay unchanged.');
assertSame(expected: '/tmp/other-cache', actual: $changed->cacheDir, message: 'Changed config should carry the new cache dir.');
assertTrue(condition: ! $changed->debug, message: 'Changed config should carry the new debug flag.');
assertTrue(condition: ! $changed->strict, message: 'Changed config should carry the new strict flag.');
assertSame(expected: CreateContainerConfig::COMPILE_MODE_DEV, actual: $changed->compileMode, message: 'Changed config should carry the new compile mode.');
assertSame(expected: CreateContainerConfig::DIAGNOSTICS_MODE_MINIMAL, actual: $changed->diagnosticsMode, message: 'Changed config should carry the new diagnostics mode.');
assertSame(expected: CreateContainerConfig::EXECUTION_MODE_DYNAMIC, actual: $changed->executionMode, message: 'Changed config should carry the new execution mode.');
assertSame(expected: CreateContainerConfig::PRUNE_MODE_NONE, actual: $changed->pruneMode, message: 'Changed config should carry the new prune mode.');
assertSame(expected: CreateContainerConfig::POLICY_PROFILE_RELAXED, actual: $changed->policyProfile, message: 'Changed config should carry the new policy profile.');
assertSame(expected: CreateContainerConfig::POLICY_FAIL_MODE_CLOSED, actual: $changed->policyFailMode, message: 'Changed config should carry the new policy fail mode.');
assertSame(expected: ['dev' => CreateContainerConfig::POLICY_PROFILE_RELAXED], actual: $changed->policyProfiles, message: 'Changed config should carry the new policy profile map.');
assertSame(expected: CreateContainerConfig::SLICE_BOUNDARY_MODE_STRICT, actual: $changed->sliceBoundaryMode, message: 'Changed config should carry the new slice boundary mode.');
assertSame(expected: CreateContainerConfig::ASYNC_TARGET_FPM, actual: $changed->asyncTarget, message: 'Changed config should carry the new async target.');
assertSame(expected: 'Other', actual: $changed->settings['app']['name'], message: 'Changed config should carry the new settings.');
assertTrue(condition: $config->validatesBeforeCompile(), message: 'CI mode should validate before compile.');
assertTrue(condition: ! $changed->validatesCompiledArtifactsOnLoad(), message: 'Dynamic mode should skip compiled artifact validation on load because the hot path stays disabled.');
assertTrue(condition: $config->validatesCompiledArtifactsOnLoad(), message: 'Generated execution should still validate freshness and compatibility before the hot path attaches.');
assertTrue(condition: $config->failsClosedOnCompiledCorruption(), message: 'Strict config should fail closed on compiled corruption.');
assertTrue(condition: $config->usesDetailedDiagnostics(), message: 'CI diagnostics mode should opt into high-detail observability.');
assertTrue(condition: ! $changed->usesDetailedDiagnostics(), message: 'Minimal diagnostics mode without debug should disable high-detail observability.');
assertTrue(condition: $config->usesGeneratedExecution(), message: 'Generated execution mode helper should stay accurate.');
assertTrue(condition: ! $config->usesDynamicExecution(), message: 'Generated execution mode should not report as dynamic.');
assertTrue(condition: $changed->usesDynamicExecution(), message: 'Dynamic execution mode helper should stay accurate.');
assertTrue(condition: $config->usesStrictPruning(), message: 'Strict pruning helper should stay accurate.');
assertTrue(condition: ! $changed->usesStrictPruning(), message: 'Disabled pruning helper should stay accurate.');
assertTrue(condition: $config->supportsAsyncTarget(), message: 'Worker async target should be supported.');
assertSame(expected: CreateContainerConfig::POLICY_PROFILE_STRICT, actual: $config->effectivePolicyProfile(), message: 'Environment policy overrides should win when they match the active environment.');
assertTrue(condition: ! $config->failsClosedOnPolicy(), message: 'Open policy fail mode should stay explicit.');
assertTrue(condition: ! $config->usesStrictSliceBoundaries(), message: 'Projected slice boundary mode should stay explicit.');
assertTrue(condition: $changed->failsClosedOnPolicy(), message: 'Closed policy fail mode helper should stay accurate.');
assertTrue(condition: $changed->usesStrictSliceBoundaries(), message: 'Strict slice boundary helper should stay accurate.');
assertTrue(condition: $config->configHash() !== '', message: 'Config hash should be available for compile invalidation.');
assertTrue(condition: $config->settingsFingerprint() !== '', message: 'Settings fingerprint should be available for metadata provenance.');
assertSame(expected: 'ci-smoke', actual: $config->benchmarkBuildMarker(), message: 'Benchmark build marker should be available for benchmark artifacts and compile metadata.');

echo basename(path: __FILE__) . " ok\n";

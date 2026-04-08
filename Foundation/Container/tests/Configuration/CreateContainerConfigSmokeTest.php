<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use Avax\Container\Configuration\CreateContainerConfig;

$config = CreateContainerConfig::create(
    cacheDir: '/tmp/container-cache',
    debug: true,
    settings: ['app' => ['name' => 'Container'], 'benchmark' => ['build_marker' => 'ci-smoke']],
    strict: true,
    compileMode: CreateContainerConfig::COMPILE_MODE_CI,
    diagnosticsMode: CreateContainerConfig::DIAGNOSTICS_MODE_CI
);

$changed = $config
    ->withCacheDir('/tmp/other-cache')
    ->withDebug(false)
    ->withStrict(false)
    ->withCompileMode(CreateContainerConfig::COMPILE_MODE_DEV)
    ->withDiagnosticsMode(CreateContainerConfig::DIAGNOSTICS_MODE_MINIMAL)
    ->withSettings(['app' => ['name' => 'Other']]);

assertSame('/tmp/container-cache', $config->cacheDir, 'Original config must stay immutable.');
assertTrue($config->debug, 'Original debug flag must stay unchanged.');
assertTrue($config->strict, 'Original strict flag must stay unchanged.');
assertSame(CreateContainerConfig::COMPILE_MODE_CI, $config->compileMode, 'Original compile mode must stay unchanged.');
assertSame(CreateContainerConfig::DIAGNOSTICS_MODE_CI, $config->diagnosticsMode, 'Original diagnostics mode must stay unchanged.');
assertSame('Container', $config->settings['app']['name'], 'Original settings must stay unchanged.');
assertSame('/tmp/other-cache', $changed->cacheDir, 'Changed config should carry the new cache dir.');
assertTrue(! $changed->debug, 'Changed config should carry the new debug flag.');
assertTrue(! $changed->strict, 'Changed config should carry the new strict flag.');
assertSame(CreateContainerConfig::COMPILE_MODE_DEV, $changed->compileMode, 'Changed config should carry the new compile mode.');
assertSame(CreateContainerConfig::DIAGNOSTICS_MODE_MINIMAL, $changed->diagnosticsMode, 'Changed config should carry the new diagnostics mode.');
assertSame('Other', $changed->settings['app']['name'], 'Changed config should carry the new settings.');
assertTrue($config->validatesBeforeCompile(), 'CI mode should validate before compile.');
assertTrue($changed->validatesCompiledArtifactsOnLoad(), 'Dev mode should validate compiled artifacts on load.');
assertTrue($config->validatesCompiledArtifactsOnLoad(), 'Production-style artifact loading should still validate freshness and compatibility before the hot path attaches.');
assertTrue($config->failsClosedOnCompiledCorruption(), 'Strict config should fail closed on compiled corruption.');
assertTrue($config->usesDetailedDiagnostics(), 'CI diagnostics mode should opt into high-detail observability.');
assertTrue(! $changed->usesDetailedDiagnostics(), 'Minimal diagnostics mode without debug should disable high-detail observability.');
assertTrue($config->configHash() !== '', 'Config hash should be available for compile invalidation.');
assertTrue($config->settingsFingerprint() !== '', 'Settings fingerprint should be available for metadata provenance.');
assertSame('ci-smoke', $config->benchmarkBuildMarker(), 'Benchmark build marker should be available for benchmark artifacts and compile metadata.');

echo basename(__FILE__) . " ok\n";

<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;
use Avax\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;

final class IntegrityDependency
{
    public function value() : string
    {
        return 'ok';
    }
}

final class IntegrityTarget
{
    public function __construct(public IntegrityDependency $dependency)
    {
    }
}

$productionCache = sys_get_temp_dir() . '/container-integrity-prod-' . uniqid();
$productionVersion = 'compiled-integrity-production';
$productionConfig = CreateContainerConfig::create(
    cacheDir: $productionCache,
    cacheVersion: $productionVersion,
    compileMode: CreateContainerConfig::COMPILE_MODE_PRODUCTION
);
$productionArtifactDir = $productionCache . '/container/' . rawurlencode($productionVersion) . '/compiled';
$productionArtifact = $productionArtifactDir . '/container.php';
$productionMetadata = $productionArtifactDir . '/container.json';

$production = makeTestContainer($productionConfig);
$production->singleton(IntegrityDependency::class, IntegrityDependency::class);
$production->compileContainer([IntegrityTarget::class, IntegrityDependency::class]);

assertTrue(is_file($productionArtifact), 'Production compile should write a compiled container artifact.');
assertTrue(is_file($productionMetadata), 'Production compile should write compiled container metadata.');

$productionMeta = json_decode((string) file_get_contents($productionMetadata), true);
assertTrue(is_array($productionMeta), 'Compiled container metadata should be machine-readable JSON.');
assertTrue(isset($productionMeta['checksum']), 'Compiled container metadata should include a checksum.');

file_put_contents($productionArtifact, "<?php\n\nreturn ['broken' => true];\n");

$productionReload = makeTestContainer($productionConfig);
$productionReload->singleton(IntegrityDependency::class, IntegrityDependency::class);

assertThrows(
/**
 * @throws \Psr\Container\ContainerExceptionInterface
 * @throws \Psr\Container\NotFoundExceptionInterface
 */ ContainerException::class,
    static fn() => $productionReload->get(IntegrityTarget::class),
    'Production mode should fail closed when the compiled artifact is corrupted.'
);
assertTrue(
    glob($productionArtifactDir . '/quarantine/*.php') !== [],
    'Corrupted production artifacts should be quarantined instead of silently reused.'
);
assertTrue(
    glob($productionArtifactDir . '/quarantine/*.json') !== [],
    'Corrupted production metadata should also be quarantined.'
);

$developmentCache = sys_get_temp_dir() . '/container-integrity-dev-' . uniqid();
$developmentVersion = 'compiled-integrity-development';
$developmentConfig = CreateContainerConfig::create(
    cacheDir: $developmentCache,
    cacheVersion: $developmentVersion,
    compileMode: CreateContainerConfig::COMPILE_MODE_DEV
);
$developmentArtifact = $developmentCache
    . '/container/' . rawurlencode($developmentVersion) . '/compiled/container.php';

$development = makeTestContainer($developmentConfig);
$development->singleton(IntegrityDependency::class, IntegrityDependency::class);
$development->compileContainer([IntegrityTarget::class, IntegrityDependency::class]);
file_put_contents($developmentArtifact, "<?php\n\nreturn ['broken' => true];\n");

$developmentReload = makeTestContainer($developmentConfig);
$developmentReload->singleton(IntegrityDependency::class, IntegrityDependency::class);
$resolved = $developmentReload->get(IntegrityTarget::class);

assertInstanceOf(
    IntegrityTarget::class,
    $resolved,
    'Development mode should fall back to dynamic resolution when the compiled artifact is corrupted.'
);
assertSame('ok', $resolved->dependency->value(), 'Fallback resolution should preserve service behavior.');

echo basename(__FILE__) . " ok\n";

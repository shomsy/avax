<?php

declare(strict_types=1);

require_once dirname(2, path: __DIR__) . '/bootstrap.php';

use Avax\Components\Application\Container\System\Capabilities\Composition\CreateContainerConfig;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Errors\ContainerException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

final class CompiledIntegritySmokeTest
{
    public function value(): string
    {
        return 'ok';
    }
}

final class IntegrityTarget
{
    public function __construct(public IntegrityDependency $integrityDependency)
    {
    }
}

$productionCache   = sys_get_temp_dir() . '/container-integrity-prod-' . uniqid();
$productionVersion = 'compiled-integrity-production';
$productionConfig  = CreateContainerConfig::create(
    cacheDir    : $productionCache,
    cacheVersion: $productionVersion,
    compileMode : CreateContainerConfig::COMPILE_MODE_PRODUCTION,
);
$productionArtifactDir = $productionCache . '/container/' . rawurlencode(string: $productionVersion) . '/compiled';
$productionArtifact    = $productionArtifactDir . '/container.php';
$productionMetadata    = $productionArtifactDir . '/container.json';

$production = makeTestContainer(config: $productionConfig);
$production->singleton(abstract: IntegrityDependency::class, concrete: IntegrityDependency::class);
$production->compileContainer(serviceIds: [IntegrityTarget::class, IntegrityDependency::class]);

assertTrue(condition: is_file(filename: $productionArtifact), message: 'Production compile should write a compiled container artifact.');
assertTrue(condition: is_file(filename: $productionMetadata), message: 'Production compile should write compiled container metadata.');

$productionMeta = json_decode(json: (string) file_get_contents(filename: $productionMetadata), associative: true);
assertTrue(condition: is_array(value: $productionMeta), message: 'Compiled container metadata should be machine-readable JSON.');
assertTrue(condition: isset($productionMeta['checksum']), message: 'Compiled container metadata should include a checksum.');

file_put_contents(filename: $productionArtifact, data: "<?php\n\nreturn ['broken' => true];\n");

$productionReload = makeTestContainer(config: $productionConfig);
$productionReload->singleton(abstract: IntegrityDependency::class, concrete: IntegrityDependency::class);

assertThrows(
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */ /**
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */ /**
 * @throws Throwable
 */ /**
 * @throws Throwable
 */
    expectedClass: ContainerException::class,
    callback     : static fn () => $productionReload->get(id: IntegrityTarget::class),
    message      : 'Production mode should fail closed when the compiled artifact is corrupted.',
);
assertTrue(
    condition: glob(pattern: $productionArtifactDir . '/quarantine/*.php') !== [],
    message  : 'Corrupted production artifacts should be quarantined instead of silently reused.',
);
assertTrue(
    condition: glob(pattern: $productionArtifactDir . '/quarantine/*.json') !== [],
    message  : 'Corrupted production metadata should also be quarantined.',
);

$developmentCache   = sys_get_temp_dir() . '/container-integrity-dev-' . uniqid();
$developmentVersion = 'compiled-integrity-development';
$developmentConfig  = CreateContainerConfig::create(
    cacheDir    : $developmentCache,
    cacheVersion: $developmentVersion,
    compileMode : CreateContainerConfig::COMPILE_MODE_DEV,
);
$developmentArtifact = $developmentCache
    . '/container/' . rawurlencode(string: $developmentVersion) . '/compiled/container.php';

$development = makeTestContainer(config: $developmentConfig);
$development->singleton(abstract: IntegrityDependency::class, concrete: IntegrityDependency::class);
$development->compileContainer(serviceIds: [IntegrityTarget::class, IntegrityDependency::class]);
file_put_contents(filename: $developmentArtifact, data: "<?php\n\nreturn ['broken' => true];\n");

$developmentReload = makeTestContainer(config: $developmentConfig);
$developmentReload->singleton(abstract: IntegrityDependency::class, concrete: IntegrityDependency::class);
$resolved = $developmentReload->get(id: IntegrityTarget::class);

assertInstanceOf(
    expectedClass: IntegrityTarget::class,
    value        : $resolved,
    message      : 'Development mode should fall back to dynamic resolution when the compiled artifact is corrupted.',
);
assertSame(expected: 'ok', actual: $resolved->dependency->value(), message: 'Fallback resolution should preserve service behavior.');

echo basename(path: __FILE__) . " ok\n";

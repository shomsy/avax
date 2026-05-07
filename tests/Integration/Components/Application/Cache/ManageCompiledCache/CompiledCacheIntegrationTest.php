<?php

declare(strict_types=1);

namespace Avax\Tests\Integration\Components\Application\Cache\ManageCompiledCache;

use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheContract;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheSources;
use Avax\Components\Application\Cache\System\Configuration\CompiledCacheConfiguration\BuildCompiledCache;
use Avax\Components\Application\Cache\System\Configuration\CompiledCacheConfiguration\CompiledCacheConfiguration;
use Override;
use PHPUnit\Framework\TestCase;

final class UserController {}

final class CompiledCacheIntegrationTest extends TestCase
{
    private string $tmpDir;

    private CompiledCacheContract $compiledCacheContract;

    public function test_it_compiles_reads_and_clears_route_like_artifact() : void
    {
        $name    = 'routes';
        $builder = static fn () : array => [
            'GET /users'  => ['controller' => UserController::class, 'method' => 'index'],
            'POST /users' => ['controller' => UserController::class, 'method' => 'store'],
        ];

        $compiledCacheSources = CompiledCacheSources::empty();

        $compiledCacheArtifact = $this->compiledCacheContract->compile(name: $name, build: $builder, sources: $compiledCacheSources);

        $this->assertSame(expected: 'routes', actual: $compiledCacheArtifact->name->toString());
        $this->assertFileExists(filename: $compiledCacheArtifact->path->toString());

        $value = $this->compiledCacheContract->read(name: $name, build: $builder, sources: $compiledCacheSources);

        $this->assertIsArray(actual: $value);
        $this->assertSame(expected: 'index', actual: $value['GET /users']['method']);

        $this->compiledCacheContract->clear(name: $name);

        $this->assertFileDoesNotExist(filename: $compiledCacheArtifact->path->toString());
    }

    public function test_it_rebuilds_when_source_file_changes() : void
    {
        $sourceFile = $this->tmpDir . '/source.php';
        file_put_contents($sourceFile, '<?php return ["version" => 1];');

        $name    = 'config';
        $builder = static fn () => require $sourceFile;

        $compiledCacheSources = CompiledCacheSources::fromPaths($sourceFile);

        $this->compiledCacheContract->compile(name: $name, build: $builder, sources: $compiledCacheSources);

        sleep(1);
        touch($sourceFile, time());

        file_put_contents($sourceFile, '<?php return ["version" => 2];');

        $builderNew              = static fn () => require $sourceFile;
        $compiledCacheSourcesNew = CompiledCacheSources::fromPaths($sourceFile);
        $value                   = $this->compiledCacheContract->read(name: $name, build: $builderNew, sources: $compiledCacheSourcesNew);

        $this->assertSame(expected: 2, actual: $value['version']);
    }

    #[Override]
    protected function setUp() : void
    {
        $this->tmpDir = sys_get_temp_dir() . '/compiled_integration_' . uniqid();
        mkdir($this->tmpDir);

        $compiledCacheConfiguration = CompiledCacheConfiguration::inDirectory($this->tmpDir);
        $buildCompiledCache         = new BuildCompiledCache();

        $this->compiledCacheContract = $buildCompiledCache->fromConfiguration($compiledCacheConfiguration);
    }

    #[Override]
    protected function tearDown() : void
    {
        $this->recursiveDelete(dir: $this->tmpDir);
    }

    private function recursiveDelete(string $dir) : void
    {
        if (! is_dir($dir)) {
            return;
        }

        foreach (glob($dir . '/*') as $file) {
            is_dir($file) ? $this->recursiveDelete(dir: $file) : unlink($file);
        }

        rmdir($dir);
    }
}

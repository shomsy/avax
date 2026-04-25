<?php

declare(strict_types=1);

namespace Avax\Cache\Tests\Integration\Cache\ManageCompiledCache;

use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCache;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheSources;
use Avax\Cache\System\Configuration\BuildCompiledCache;
use Avax\Cache\System\Configuration\CompiledCacheConfiguration;
use PHPUnit\Framework\TestCase;

final class CompiledCacheIntegrationTest extends TestCase
{
    private string        $tmpDir;
    private CompiledCache $cache;

    public function test_it_compiles_reads_and_clears_route_like_artifact() : void
    {
        $name    = 'routes';
        $builder = fn () => [
            'GET /users'  => ['controller' => UserController::class, 'method' => 'index'],
            'POST /users' => ['controller' => UserController::class, 'method' => 'store'],
        ];

        $sources = CompiledCacheSources::empty();

        $artifact = $this->cache->compile($name, $builder, $sources);

        $this->assertSame('routes', $artifact->name->toString());
        $this->assertFileExists($artifact->path->toString());

        $value = $this->cache->read($name, $builder, $sources);

        $this->assertIsArray($value);
        $this->assertSame('index', $value['GET /users']['method']);

        $this->cache->clear($name);

        $this->assertFileDoesNotExist($artifact->path->toString());
    }

    public function test_it_rebuilds_when_source_file_changes() : void
    {
        $sourceFile = $this->tmpDir . '/source.php';
        file_put_contents($sourceFile, '<?php return ["version" => 1];');

        $name    = 'config';
        $builder = fn () => require $sourceFile;

        $sources = CompiledCacheSources::fromPaths($sourceFile);

        $this->cache->compile($name, $builder, $sources);

        sleep(1);
        touch($sourceFile, time());

        file_put_contents($sourceFile, '<?php return ["version" => 2];');

        $builderNew = fn () => require $sourceFile;
        $value      = $this->cache->read($name, $builderNew, $sources);

        $this->assertSame(2, $value['version']);
    }

    protected function setUp() : void
    {
        $this->tmpDir = sys_get_temp_dir() . '/compiled_integration_' . uniqid();
        mkdir($this->tmpDir);

        $config  = CompiledCacheConfiguration::inDirectory($this->tmpDir);
        $builder = new BuildCompiledCache();

        $this->cache = $builder->fromConfiguration($config);
    }

    protected function tearDown() : void
    {
        $this->recursiveDelete($this->tmpDir);
    }

    private function recursiveDelete(string $dir) : void
    {
        if (! is_dir($dir)) {
            return;
        }

        foreach (glob("{$dir}/*") as $file) {
            is_dir($file) ? $this->recursiveDelete($file) : unlink($file);
        }

        rmdir($dir);
    }
}
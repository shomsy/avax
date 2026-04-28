<?php

declare(strict_types=1);

namespace Avax\Cache\tests\Integration\Cache\ManageCompiledCache;

use Avax\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheContract;
use Avax\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheSources;
use Avax\Cache\System\Configuration\CompiledCacheConfiguration\BuildCompiledCache;
use Avax\Cache\System\Configuration\CompiledCacheConfiguration\CompiledCacheConfiguration;
use PHPUnit\Framework\TestCase;

final class UserController
{
    public static function index() {}

    public static function store() {}
}

final class CompiledCacheIntegrationTest extends TestCase
{
    private string                $tmpDir;
    private CompiledCacheContract $cache;

    public function test_it_compiles_reads_and_clears_route_like_artifact() : void
    {
        $name    = 'routes';
        $builder = static fn () => [
            'GET /users'  => ['controller' => UserController::class, 'method' => 'index'],
            'POST /users' => ['controller' => UserController::class, 'method' => 'store'],
        ];

        $sources = CompiledCacheSources::empty();

        $artifact = $this->cache->compile(name: $name, build: $builder, sources: $sources);

        $this->assertSame(expected: 'routes', actual: $artifact->name->toString());
        $this->assertFileExists(filename: $artifact->path->toString());

        $value = $this->cache->read(name: $name, build: $builder, sources: $sources);

        $this->assertIsArray(actual: $value);
        $this->assertSame(expected: 'index', actual: $value['GET /users']['method']);

        $this->cache->clear(name: $name);

        $this->assertFileDoesNotExist(filename: $artifact->path->toString());
    }

    public function test_it_rebuilds_when_source_file_changes() : void
    {
        $sourceFile = $this->tmpDir . '/source.php';
        file_put_contents($sourceFile, '<?php return ["version" => 1];');

        $name    = 'config';
        $builder = static fn () => require $sourceFile;

        $sources = CompiledCacheSources::fromPaths($sourceFile);

        $this->cache->compile(name: $name, build: $builder, sources: $sources);

        sleep(1);
        touch($sourceFile, time());

        file_put_contents($sourceFile, '<?php return ["version" => 2];');

        $builderNew = static fn () => require $sourceFile;
        $value      = $this->cache->read(name: $name, build: $builderNew, sources: $sources);

        $this->assertSame(expected: 2, actual: $value['version']);
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
        $this->recursiveDelete(dir: $this->tmpDir);
    }

    private function recursiveDelete(string $dir) : void
    {
        if (! is_dir($dir)) {
            return;
        }

        foreach (glob("{$dir}/*") as $file) {
            is_dir($file) ? $this->recursiveDelete(dir: $file) : unlink($file);
        }

        rmdir($dir);
    }
}
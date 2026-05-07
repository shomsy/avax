<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Config\Capabilities\Loader;

use Avax\Components\Application\Config\System\Capabilities\Loader\ConfigLoader;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ConfigLoaderTest extends TestCase
{
    private string $tmpDir;

    private ConfigLoader $loader;

    public function test_load_file_returns_array() : void
    {
        $file = $this->tmpDir . '/app.php';
        file_put_contents($file, '<?php return ["name" => "AvaX"];');

        $result = $this->loader->load(path: $file);

        $this->assertSame(['name' => 'AvaX'], $result);
    }

    public function test_load_directory_returns_namespaced_array() : void
    {
        $appFile = $this->tmpDir . '/app.php';
        $dbFile  = $this->tmpDir . '/database.php';
        file_put_contents($appFile, '<?php return ["name" => "AvaX"];');
        file_put_contents($dbFile, '<?php return ["driver" => "sqlite"];');

        $result = $this->loader->load(path: $this->tmpDir);

        $this->assertArrayHasKey('app', $result);
        $this->assertArrayHasKey('database', $result);
        $this->assertSame('AvaX', $result['app']['name']);
        $this->assertSame('sqlite', $result['database']['driver']);
    }

    public function test_load_throws_exception_if_file_missing() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Config file not found');

        $this->loader->load(path: $this->tmpDir . '/missing.php');
    }

    public function test_load_throws_exception_if_not_array() : void
    {
        $file = $this->tmpDir . '/invalid.php';
        file_put_contents($file, '<?php return "not an array";');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Config file must return an array');

        $this->loader->load(path: $file);
    }

    protected function setUp() : void
    {
        $this->tmpDir = sys_get_temp_dir() . '/config_loader_test_' . uniqid();
        mkdir($this->tmpDir);
        $this->loader = new ConfigLoader();
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

        foreach (glob($dir . '/*') ?: [] as $file) {
            is_dir($file) ? $this->recursiveDelete($file) : unlink($file);
        }

        rmdir($dir);
    }
}

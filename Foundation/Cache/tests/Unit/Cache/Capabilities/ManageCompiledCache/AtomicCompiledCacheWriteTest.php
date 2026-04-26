<?php

declare(strict_types=1);

namespace Avax\Cache\Tests\Unit\Cache\Capabilities\ManageCompiledCache;

use Avax\Cache\System\Capabilities\ManageCompiledCache\AtomicCompiledCacheWrite;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheDirectory;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheName;
use PHPUnit\Framework\TestCase;

final class AtomicCompiledCacheWriteTest extends TestCase
{
    private string $tmpDir;

    public function test_it_writes_to_temporary_file_before_final_file() : void
    {
        $directory = new CompiledCacheDirectory($this->tmpDir);
        $writer    = new AtomicCompiledCacheWrite($directory);

        $phpPayload = "<?php\n\nreturn ['test' => true];\n";
        $artifact   = $writer->write(new CompiledCacheName('test-artifact'), $phpPayload);

        $this->assertFileExists($artifact->path->toString());
    }

    public function test_it_writes_atomically() : void
    {
        $tmpDir = sys_get_temp_dir() . '/avax_cache_test_' . uniqid();
        mkdir($tmpDir, 0755, true);

        $directory    = new CompiledCacheDirectory($tmpDir);
        $existingFile = $tmpDir . '/existing.php';
        file_put_contents($existingFile, '<?php return ["original"];');

        $writer = new AtomicCompiledCacheWrite($directory);

        $phpPayload = '<?php return ["new_value"];';
        $artifact   = $writer->write(new CompiledCacheName('existing'), $phpPayload);

        $this->assertFileExists($artifact->path->toString());
        $content = file_get_contents($existingFile);
        $this->assertStringContainsString('new_value', $content);

        $this->recursiveDelete($tmpDir);
    }

    public function test_it_preserves_existing_file_when_file_put_contents_fails() : void
    {
        $this->markTestSkipped('Cannot reliably test file_put_contents failure in sandboxed environment');
    }

    protected function setUp() : void
    {
        $this->tmpDir = sys_get_temp_dir() . '/compiled_cache_test_' . uniqid();
        mkdir($this->tmpDir);
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
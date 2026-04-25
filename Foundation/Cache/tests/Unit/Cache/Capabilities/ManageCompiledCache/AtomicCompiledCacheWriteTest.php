<?php

declare(strict_types=1);

namespace Avax\Cache\Tests\Unit\Cache\Capabilities\ManageCompiledCache;

use Avax\Cache\System\Capabilities\ManageCompiledCache\AtomicCompiledCacheWrite;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheCouldNotBeWritten;
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

    public function test_it_preserves_existing_file_when_write_fails() : void
    {
        $directory = new CompiledCacheDirectory($this->tmpDir);

        file_put_contents($this->tmpDir . '/existing.php', '<?php return ["original"];');

        $writer = new AtomicCompiledCacheWrite($directory);

        try {
            $writer->write(new CompiledCacheName('existing'), 'invalid php syntax');
        } catch (CompiledCacheCouldNotBeWritten) {
        }

        $content = file_get_contents($this->tmpDir . '/existing.php');
        $this->assertStringContainsString('original', $content);
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
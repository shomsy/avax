<?php

declare(strict_types=1);

namespace Avax\Cache\Tests\Unit\Cache\Capabilities\ManageCompiledCache;

use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheSource;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CompiledCacheSourceTest extends TestCase
{
    private string $tmpFile;

    public function test_it_creates_source_from_path() : void
    {
        $source = CompiledCacheSource::fromPath($this->tmpFile);

        $this->assertSame($this->tmpFile, $source->path);
        $this->assertGreaterThan(0, $source->mtime);
    }

    public function test_it_generates_fingerprint() : void
    {
        $source = CompiledCacheSource::fromPath($this->tmpFile);

        $fingerprint = $source->fingerprint();

        $this->assertIsString($fingerprint);
        $this->assertNotEmpty($fingerprint);
    }

    public function test_it_rejects_nonexistent_file() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not exist');

        CompiledCacheSource::fromPath('/nonexistent/file.php');
    }

    protected function setUp() : void
    {
        $this->tmpFile = sys_get_temp_dir() . '/test_source_' . uniqid() . '.php';
        file_put_contents($this->tmpFile, '<?php return [];');
    }

    protected function tearDown() : void
    {
        if (file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }
    }
}
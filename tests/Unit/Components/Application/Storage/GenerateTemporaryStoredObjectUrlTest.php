<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Storage;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\Application\Storage\System\Capabilities\Disks\LocalDisk\LocalDisk;
use Avax\Components\Application\Storage\System\Flows\GenerateTemporaryStoredObjectUrl\GenerateTemporaryStoredObjectUrl;
use Avax\Components\Application\Storage\System\Foundation\Failure\TemporaryUrlNotSupported;
use Avax\Components\Application\Storage\System\Foundation\Values\StoragePath;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * GenerateTemporaryStoredObjectUrl flow failure test.
 *
 * Proves that the flow correctly checks supportsTemporaryUrl before
 * attempting delegation and throws TemporaryUrlNotSupported for LocalDisk.
 */
final class GenerateTemporaryStoredObjectUrlTest extends TestCase
{
    private string     $tempDir;
    private Filesystem $filesystem;
    private LocalDisk  $localDisk;

    public function testThrowsTemporaryUrlNotSupportedForLocalDisk() : void
    {
        $flow = new GenerateTemporaryStoredObjectUrl($this->localDisk);

        $this->expectException(TemporaryUrlNotSupported::class);
        $this->expectExceptionMessage('Temporary URLs not supported for disk: local');

        $flow->execute('file.txt', new DateTimeImmutable('+1 hour'));
    }

    public function testThrowsTemporaryUrlNotSupportedForExistingFile() : void
    {
        $this->localDisk->write(
            new StoragePath('existing.txt'),
            'data'
        );

        $flow = new GenerateTemporaryStoredObjectUrl($this->localDisk);

        $this->expectException(TemporaryUrlNotSupported::class);

        $flow->execute('existing.txt', new DateTimeImmutable('+1 hour'));
    }

    public function testThrowsTemporaryUrlNotSupportedForNestedPath() : void
    {
        $flow = new GenerateTemporaryStoredObjectUrl($this->localDisk);

        $this->expectException(TemporaryUrlNotSupported::class);

        $flow->execute('nested/deep/file.txt', new DateTimeImmutable('+24 hours'));
    }

    public function testThrowsTemporaryUrlNotSupportedWithPastExpiry() : void
    {
        $flow = new GenerateTemporaryStoredObjectUrl($this->localDisk);

        $this->expectException(TemporaryUrlNotSupported::class);

        $flow->execute('file.txt', new DateTimeImmutable('-1 hour'));
    }

    public function testThrowsTemporaryUrlNotSupportedWithVeryLongExpiry() : void
    {
        $flow = new GenerateTemporaryStoredObjectUrl($this->localDisk);

        $this->expectException(TemporaryUrlNotSupported::class);

        $flow->execute('file.txt', new DateTimeImmutable('+365 days'));
    }

    public function testThrowsTemporaryUrlNotSupportedWithEmptyPath() : void
    {
        $flow = new GenerateTemporaryStoredObjectUrl($this->localDisk);

        $this->expectException(TemporaryUrlNotSupported::class);

        $flow->execute('', new DateTimeImmutable('+1 hour'));
    }

    public function testExceptionContainsDiskName() : void
    {
        $flow = new GenerateTemporaryStoredObjectUrl($this->localDisk);

        try {
            $flow->execute('file.txt', new DateTimeImmutable('+1 hour'));
            self::fail('Expected TemporaryUrlNotSupported exception');
        } catch (TemporaryUrlNotSupported $e) {
            self::assertSame('local', $e->diskName);
        }
    }

    protected function setUp() : void
    {
        $this->tempDir = sys_get_temp_dir() . '/avax_temp_url_test_' . uniqid();
        mkdir($this->tempDir, 0o755, true);

        $this->filesystem = new Filesystem();
        $this->localDisk  = new LocalDisk($this->filesystem, $this->tempDir);
    }

    protected function tearDown() : void
    {
        $this->removeDirectoryRecursive($this->tempDir);
    }

    private function removeDirectoryRecursive(string $path) : void
    {
        if (! is_dir($path)) {
            return;
        }

        $items = scandir($path);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $itemPath = $path . '/' . $item;

            if (is_dir($itemPath)) {
                $this->removeDirectoryRecursive($itemPath);
            } else {
                unlink($itemPath);
            }
        }

        rmdir($path);
    }
}

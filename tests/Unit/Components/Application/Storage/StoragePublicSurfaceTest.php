<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Storage;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\Application\Storage\System\Capabilities\Disks\Disk;
use Avax\Components\Application\Storage\System\Capabilities\Disks\LocalDisk\LocalDisk;
use Avax\Components\Application\Storage\System\Capabilities\Disks\RegisteredDisks;
use Avax\Components\Application\Storage\System\Capabilities\Disks\ResolveDisk;
use Avax\Components\Application\Storage\System\Foundation\Failure\DiskNotFound;
use Avax\Components\Application\Storage\System\Foundation\Failure\TemporaryUrlNotSupported;
use Avax\Components\Application\Storage\System\Foundation\Values\DiskName;
use Avax\Components\Application\Storage\System\Foundation\Values\StoragePath;
use Avax\Components\Application\Storage\System\PublicSurface\Storage;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class StoragePublicSurfaceTest extends TestCase
{
    private string          $tempDir;
    private Filesystem      $filesystem;
    private RegisteredDisks $registry;

    public function testDiskReturnsDiskInterface() : void
    {
        $disk = $this->registry->get('local');

        $this->assertInstanceOf(Disk::class, $disk);
    }

    public function testDiskThrowsOnUnknownDisk() : void
    {
        $this->expectException(DiskNotFound::class);

        $resolve = new ResolveDisk($this->registry);
        $resolve->execute('unknown_disk');
    }

    public function testPutAndGetObject() : void
    {
        $disk = new LocalDisk($this->filesystem, $this->tempDir);

        $path    = 'test_file.txt';
        $content = 'hello storage';

        $result = $disk->write(new StoragePath($path), $content);
        $this->assertTrue($result);

        $readContent = $disk->read(new StoragePath($path));
        $this->assertSame($content, $readContent);
    }

    public function testExistsAndDeleteObject() : void
    {
        $disk = new LocalDisk($this->filesystem, $this->tempDir);
        $path = new StoragePath('exists_test.txt');

        $this->assertFalse($disk->exists($path));

        $disk->write($path, 'content');
        $this->assertTrue($disk->exists($path));

        $disk->delete($path);
        $this->assertFalse($disk->exists($path));
    }

    public function testCopyObject() : void
    {
        $disk   = new LocalDisk($this->filesystem, $this->tempDir);
        $source = new StoragePath('copy_source.txt');
        $dest   = new StoragePath('copy_dest.txt');

        $disk->write($source, 'original content');

        $result = $disk->copy($source, $dest);

        $this->assertTrue($result);
        $this->assertSame('original content', $disk->read($dest));
    }

    public function testMoveObject() : void
    {
        $disk   = new LocalDisk($this->filesystem, $this->tempDir);
        $source = new StoragePath('move_source.txt');
        $dest   = new StoragePath('moved_file.txt');

        $disk->write($source, 'to be moved');

        $result = $disk->move($source, $dest);

        $this->assertTrue($result);
        $this->assertSame('to be moved', $disk->read($dest));
        $this->assertFalse($disk->exists($source));
    }

    public function testLocalDiskUsesFilesystem() : void
    {
        $disk = new LocalDisk($this->filesystem, $this->tempDir);

        $testFile = $this->tempDir . '/composition_test.txt';
        file_put_contents($testFile, 'direct write');

        $this->assertTrue($disk->exists(new StoragePath('composition_test.txt')));
        $this->assertSame('direct write', $disk->read(new StoragePath('composition_test.txt')));
    }

    public function testLocalDiskDoesNotBypassFilesystem() : void
    {
        $disk = new LocalDisk($this->filesystem, $this->tempDir);

        $disk->write(new StoragePath('written_via_disk.txt'), 'disk content');

        $this->assertTrue($this->filesystem->exists($this->tempDir . '/written_via_disk.txt'));
    }

    public function testTemporaryUrlThrowsOnLocalDisk() : void
    {
        $disk = new LocalDisk($this->filesystem, $this->tempDir);

        $this->assertFalse($disk->supportsTemporaryUrl());

        $this->expectException(TemporaryUrlNotSupported::class);
        $disk->temporaryUrl(new StoragePath('file.txt'), new DateTimeImmutable('+1 hour'));
    }

    public function testUrlReturnsFileProtocol() : void
    {
        $disk = new LocalDisk($this->filesystem, $this->tempDir);

        $url = $disk->url(new StoragePath('some_file.txt'));

        $this->assertStringStartsWith('file://', $url);
    }

    public function testStorageDoesNotImportFilesystemFromStorage() : void
    {
        $storageReflection = new ReflectionClass(Storage::class);

        $this->assertNotContains(
            Filesystem::class,
            $storageReflection->getTraitNames(),
            'Storage should not use Filesystem directly in its class hierarchy'
        );
    }

    protected function setUp() : void
    {
        $this->tempDir = sys_get_temp_dir() . '/avax_storage_test_' . uniqid();
        mkdir($this->tempDir, 0o755, true);

        $this->filesystem = new Filesystem();
        $this->registry   = new RegisteredDisks();

        $disk = new LocalDisk($this->filesystem, $this->tempDir);
        $this->registry->register(new DiskName('local'), $disk);
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
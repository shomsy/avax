<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Storage;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\Application\Storage\System\Capabilities\Disks\Disk;
use Avax\Components\Application\Storage\System\Capabilities\Disks\LocalDisk\LocalDisk;
use Avax\Components\Application\Storage\System\Capabilities\Disks\RegisteredDisks;
use Avax\Components\Application\Storage\System\Capabilities\Disks\ResolveDisk;
use Avax\Components\Application\Storage\System\Foundation\Failure\DiskNotFound;
use Avax\Components\Application\Storage\System\Foundation\Values\DiskName;
use PHPUnit\Framework\TestCase;

/**
 * ResolveDisk flow tests.
 *
 * Proves the resolution flow correctly retrieves registered disks and
 * throws DiskNotFound for unregistered names.
 */
final class ResolveDiskTest extends TestCase
{
    private RegisteredDisks $registry;
    private string          $tempDir;
    private Filesystem      $filesystem;

    public function testResolveReturnsRegisteredDisk() : void
    {
        $disk = new LocalDisk($this->filesystem, $this->tempDir);
        $this->registry->register(new DiskName('local'), $disk);

        $resolve = new ResolveDisk($this->registry);
        $result  = $resolve->execute('local');

        self::assertInstanceOf(Disk::class, $result);
        self::assertSame($disk, $result);
    }

    public function testResolveThrowsDiskNotFoundForUnknownName() : void
    {
        $resolve = new ResolveDisk($this->registry);

        $this->expectException(DiskNotFound::class);
        $this->expectExceptionMessage('Disk not found: unknown_disk');

        $resolve->execute('unknown_disk');
    }

    public function testResolveThrowsDiskNotFoundForEmptyRegistry() : void
    {
        $resolve = new ResolveDisk($this->registry);

        $this->expectException(DiskNotFound::class);
        $this->expectExceptionMessage('Disk not found: any');

        $resolve->execute('any');
    }

    public function testResolveWithEmptyStringName() : void
    {
        $resolve = new ResolveDisk($this->registry);

        $this->expectException(DiskNotFound::class);

        $resolve->execute('');
    }

    public function testResolveDistinguishesSimilarNames() : void
    {
        $disk1 = new LocalDisk($this->filesystem, $this->tempDir . '/one');
        $disk2 = new LocalDisk($this->filesystem, $this->tempDir . '/two');

        $this->registry->register(new DiskName('disk_one'), $disk1);
        $this->registry->register(new DiskName('disk_two'), $disk2);

        $resolve = new ResolveDisk($this->registry);

        self::assertSame($disk1, $resolve->execute('disk_one'));
        self::assertSame($disk2, $resolve->execute('disk_two'));
    }

    public function testResolveAfterClearReturnsNotFound() : void
    {
        $disk = new LocalDisk($this->filesystem, $this->tempDir);
        $this->registry->register(new DiskName('temp'), $disk);

        $this->registry->clear();

        $resolve = new ResolveDisk($this->registry);

        $this->expectException(DiskNotFound::class);
        $resolve->execute('temp');
    }

    public function testResolveAfterOverwriteReturnsNewDisk() : void
    {
        $oldDisk = new LocalDisk($this->filesystem, $this->tempDir . '/old');
        $newDisk = new LocalDisk($this->filesystem, $this->tempDir . '/new');

        $this->registry->register(new DiskName('replace'), $oldDisk);
        $this->registry->register(new DiskName('replace'), $newDisk);

        $resolve = new ResolveDisk($this->registry);
        $result  = $resolve->execute('replace');

        self::assertSame($newDisk, $result);
    }

    public function testResolveIsReadonly() : void
    {
        $disk = new LocalDisk($this->filesystem, $this->tempDir);
        $this->registry->register(new DiskName('readonly'), $disk);

        $resolve = new ResolveDisk($this->registry);

        $result1 = $resolve->execute('readonly');
        $result2 = $resolve->execute('readonly');

        self::assertSame($result1, $result2);
    }

    protected function setUp() : void
    {
        $this->registry = new RegisteredDisks();
        $this->tempDir  = sys_get_temp_dir() . '/avax_resolve_disk_test_' . uniqid();
        mkdir($this->tempDir, 0o755, true);
        $this->filesystem = new Filesystem();
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

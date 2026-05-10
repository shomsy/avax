<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Storage;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\Application\Storage\System\Capabilities\Disks\Disk;
use Avax\Components\Application\Storage\System\Capabilities\Disks\LocalDisk\LocalDisk;
use Avax\Components\Application\Storage\System\Capabilities\Disks\RegisteredDisks;
use Avax\Components\Application\Storage\System\Foundation\Values\DiskName;
use PHPUnit\Framework\TestCase;

/**
 * RegisteredDisks registry tests.
 *
 * Proves the disk registry correctly handles registration, lookup,
 * enumeration, existence checks, clearing, and overwrite behavior.
 */
final class RegisteredDisksTest extends TestCase
{
    private RegisteredDisks $registry;
    private string          $tempDir;
    private Filesystem      $filesystem;

    public function testRegisterAndGet() : void
    {
        $disk = new LocalDisk($this->filesystem, $this->tempDir);
        $this->registry->register(new DiskName('my_disk'), $disk);

        $retrieved = $this->registry->get('my_disk');

        self::assertInstanceOf(Disk::class, $retrieved);
        self::assertSame($disk, $retrieved);
    }

    public function testGetReturnsNullForUnregisteredDisk() : void
    {
        self::assertNull($this->registry->get('unknown'));
    }

    public function testHasReturnsTrueForRegisteredDisk() : void
    {
        $disk = new LocalDisk($this->filesystem, $this->tempDir);
        $this->registry->register(new DiskName('check_disk'), $disk);

        self::assertTrue($this->registry->has('check_disk'));
    }

    public function testHasReturnsFalseForUnregisteredDisk() : void
    {
        self::assertFalse($this->registry->has('nope'));
    }

    public function testNamesReturnsAllRegisteredNames() : void
    {
        $this->registry->register(new DiskName('alpha'), new LocalDisk($this->filesystem, $this->tempDir . '/alpha'));
        $this->registry->register(new DiskName('beta'), new LocalDisk($this->filesystem, $this->tempDir . '/beta'));
        $this->registry->register(new DiskName('gamma'), new LocalDisk($this->filesystem, $this->tempDir . '/gamma'));

        $names = $this->registry->names();

        self::assertCount(3, $names);
        self::assertContains('alpha', $names);
        self::assertContains('beta', $names);
        self::assertContains('gamma', $names);
    }

    public function testNamesReturnsEmptyArrayWhenNoDisksRegistered() : void
    {
        self::assertSame([], $this->registry->names());
    }

    public function testOverwriteReplacesExistingDisk() : void
    {
        $disk1 = new LocalDisk($this->filesystem, $this->tempDir . '/v1');
        $disk2 = new LocalDisk($this->filesystem, $this->tempDir . '/v2');

        $this->registry->register(new DiskName('replaceable'), $disk1);
        $this->registry->register(new DiskName('replaceable'), $disk2);

        self::assertSame($disk2, $this->registry->get('replaceable'));
    }

    public function testClearRemovesAllDisks() : void
    {
        $this->registry->register(new DiskName('a'), new LocalDisk($this->filesystem, $this->tempDir . '/a'));
        $this->registry->register(new DiskName('b'), new LocalDisk($this->filesystem, $this->tempDir . '/b'));

        $this->registry->clear();

        self::assertNull($this->registry->get('a'));
        self::assertNull($this->registry->get('b'));
        self::assertFalse($this->registry->has('a'));
        self::assertFalse($this->registry->has('b'));
        self::assertSame([], $this->registry->names());
    }

    public function testClearOnEmptyRegistryIsNoOp() : void
    {
        $this->registry->clear();

        self::assertSame([], $this->registry->names());
    }

    public function testMultipleDisksAreIndependent() : void
    {
        $diskA = new LocalDisk($this->filesystem, $this->tempDir . '/disk_a');
        $diskB = new LocalDisk($this->filesystem, $this->tempDir . '/disk_b');

        $this->registry->register(new DiskName('a'), $diskA);
        $this->registry->register(new DiskName('b'), $diskB);

        $retrievedA = $this->registry->get('a');
        $retrievedB = $this->registry->get('b');

        self::assertNotSame($retrievedA, $retrievedB);
    }

    public function testRegisterWithSpecialCharacterNames() : void
    {
        $disk = new LocalDisk($this->filesystem, $this->tempDir);

        $this->registry->register(new DiskName('my-disk'), $disk);
        $this->registry->register(new DiskName('my_disk'), $disk);
        $this->registry->register(new DiskName('disk123'), $disk);

        self::assertTrue($this->registry->has('my-disk'));
        self::assertTrue($this->registry->has('my_disk'));
        self::assertTrue($this->registry->has('disk123'));
    }

    public function testRegisterWithDiskNameValueObject() : void
    {
        $disk = new LocalDisk($this->filesystem, $this->tempDir);
        $name = new DiskName('vo_disk');

        $this->registry->register($name, $disk);

        self::assertSame($disk, $this->registry->get('vo_disk'));
    }

    public function testRegistryDoesNotConfuseSimilarNames() : void
    {
        $disk1 = new LocalDisk($this->filesystem, $this->tempDir . '/one');
        $disk2 = new LocalDisk($this->filesystem, $this->tempDir . '/two');

        $this->registry->register(new DiskName('disk'), $disk1);
        $this->registry->register(new DiskName('disk-2'), $disk2);

        self::assertSame($disk1, $this->registry->get('disk'));
        self::assertSame($disk2, $this->registry->get('disk-2'));
    }

    protected function setUp() : void
    {
        $this->registry = new RegisteredDisks();
        $this->tempDir  = sys_get_temp_dir() . '/avax_registered_disks_test_' . uniqid();
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

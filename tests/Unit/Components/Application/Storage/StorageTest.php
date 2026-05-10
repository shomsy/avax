<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Storage;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\Application\Storage\System\Capabilities\Disks\Disk;
use Avax\Components\Application\Storage\System\Capabilities\Disks\LocalDisk\LocalDisk;
use Avax\Components\Application\Storage\System\Capabilities\Disks\RegisteredDisks;
use Avax\Components\Application\Storage\System\Foundation\Failure\DiskNotFound;
use Avax\Components\Application\Storage\System\Foundation\Failure\TemporaryUrlNotSupported;
use Avax\Components\Application\Storage\System\Foundation\Values\DiskName;
use Avax\Components\Application\Storage\System\PublicSurface\Storage;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Storage public surface integration tests.
 *
 * Exercises the static Storage facade end-to-end through real filesystem operations,
 * proving the full delegation chain: Storage -> Flow -> Disk -> Filesystem.
 */
final class StorageTest extends TestCase
{
    private string $tempDir;

    public function testPutWritesAndGetsContent() : void
    {
        $result = Storage::put('hello.txt', 'hello world');

        self::assertTrue($result);
        self::assertSame('hello world', Storage::get('hello.txt'));
    }

    public function testPutOverwritesExistingContent() : void
    {
        Storage::put('overwrite.txt', 'first');
        Storage::put('overwrite.txt', 'second');

        self::assertSame('second', Storage::get('overwrite.txt'));
    }

    public function testGetReturnsContentFromDisk() : void
    {
        Storage::put('readme.txt', 'read this');

        self::assertSame('read this', Storage::get('readme.txt'));
    }

    public function testGetReturnsExactBinaryContent() : void
    {
        $binary = "\x00\x01\x02\xFF\xFE";
        Storage::put('binary.bin', $binary);

        self::assertSame($binary, Storage::get('binary.bin'));
    }

    public function testExistsReturnsTrueForExistingFile() : void
    {
        Storage::put('check.txt', 'data');

        self::assertTrue(Storage::exists('check.txt'));
    }

    public function testExistsReturnsFalseForMissingFile() : void
    {
        self::assertFalse(Storage::exists('does_not_exist.txt'));
    }

    public function testDeleteRemovesFile() : void
    {
        Storage::put('to_delete.txt', 'bye');
        self::assertTrue(Storage::exists('to_delete.txt'));

        $result = Storage::delete('to_delete.txt');

        self::assertTrue($result);
        self::assertFalse(Storage::exists('to_delete.txt'));
    }

    public function testCopyDuplicatesFile() : void
    {
        Storage::put('source.txt', 'copy me');

        $result = Storage::copy('source.txt', 'destination.txt');

        self::assertTrue($result);
        self::assertSame('copy me', Storage::get('source.txt'));
        self::assertSame('copy me', Storage::get('destination.txt'));
    }

    public function testCopyPreservesSourceAfterCopy() : void
    {
        Storage::put('original.txt', 'original');
        Storage::copy('original.txt', 'backup.txt');

        self::assertTrue(Storage::exists('original.txt'));
        self::assertTrue(Storage::exists('backup.txt'));
    }

    public function testMoveRenamesFile() : void
    {
        Storage::put('old.txt', 'moved content');

        $result = Storage::move('old.txt', 'new.txt');

        self::assertTrue($result);
        self::assertFalse(Storage::exists('old.txt'));
        self::assertSame('moved content', Storage::get('new.txt'));
    }

    public function testUrlReturnsFileProtocolUrl() : void
    {
        Storage::put('url_target.txt', 'data');

        $url = Storage::url('url_target.txt');

        self::assertStringStartsWith('file://', $url);
        self::assertStringContainsString('url_target.txt', $url);
    }

    public function testTemporaryUrlThrowsForLocalDisk() : void
    {
        Storage::put('temp_target.txt', 'data');

        $this->expectException(TemporaryUrlNotSupported::class);
        $this->expectExceptionMessage('Temporary URLs not supported for disk: local');

        Storage::temporaryUrl('temp_target.txt', new DateTimeImmutable('+1 hour'));
    }

    public function testDiskReturnsRegisteredDisk() : void
    {
        $disk = Storage::disk('local');

        self::assertInstanceOf(Disk::class, $disk);
    }

    public function testDiskThrowsForUnregisteredName() : void
    {
        $this->expectException(DiskNotFound::class);
        $this->expectExceptionMessage('Disk not found: nonexistent');

        Storage::disk('nonexistent');
    }

    public function testDefaultDiskReturnsLocalByDefault() : void
    {
        $disk = Storage::defaultDisk();

        self::assertInstanceOf(Disk::class, $disk);
    }

    public function testMultipleFilesCanBeStoredIndependently() : void
    {
        Storage::put('a.txt', 'content A');
        Storage::put('b.txt', 'content B');
        Storage::put('c.txt', 'content C');

        self::assertSame('content A', Storage::get('a.txt'));
        self::assertSame('content B', Storage::get('b.txt'));
        self::assertSame('content C', Storage::get('c.txt'));
    }

    public function testPutAndGetWithEmptyContent() : void
    {
        Storage::put('empty.txt', '');

        self::assertSame('', Storage::get('empty.txt'));
    }

    public function testDeleteNonExistentFileReturnsTrueBecauseFilesystemIsIdempotent() : void
    {
        // The underlying Filesystem delete is idempotent — returns true even for missing files.
        self::assertTrue(Storage::delete('never_existed.txt'));
    }

    public function testFullLifecyclePutExistsGetDelete() : void
    {
        self::assertFalse(Storage::exists('lifecycle.txt'));

        Storage::put('lifecycle.txt', 'full cycle');
        self::assertTrue(Storage::exists('lifecycle.txt'));

        self::assertSame('full cycle', Storage::get('lifecycle.txt'));

        Storage::delete('lifecycle.txt');
        self::assertFalse(Storage::exists('lifecycle.txt'));
    }

    public function testPutAndGetWithNewlineContent() : void
    {
        $content = "line one\nline two\nline three";
        Storage::put('multiline.txt', $content);

        self::assertSame($content, Storage::get('multiline.txt'));
    }

    public function testPutAndGetWithUtf8Content() : void
    {
        $content = "Привет мир 你好世界 🌍";
        Storage::put('unicode.txt', $content);

        self::assertSame($content, Storage::get('unicode.txt'));
    }

    protected function setUp() : void
    {
        $this->tempDir = sys_get_temp_dir() . '/avax_storage_facade_test_' . uniqid();
        mkdir($this->tempDir, 0o755, true);

        Storage::registerDisk('local', new LocalDisk(new Filesystem(), $this->tempDir));
        Storage::setDefaultDisk('local');
    }

    protected function tearDown() : void
    {
        $this->removeDirectoryRecursive($this->tempDir);
        Storage::reset();
    }

    public function testResetClearsRegistryAndDefaultDisk() : void
    {
        // Arrange: register and use a disk
        Storage::put('before_reset.txt', 'content');
        self::assertTrue(Storage::exists('before_reset.txt'));

        // Act: reset
        Storage::reset();

        // Assert: registry is empty, default disk falls back but no disks registered
        $this->expectException(DiskNotFound::class);
        Storage::disk('local');
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

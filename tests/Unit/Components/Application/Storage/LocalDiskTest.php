<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Storage;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\Application\Storage\System\Capabilities\Disks\LocalDisk\LocalDisk;
use Avax\Components\Application\Storage\System\Foundation\Failure\StoredObjectNotFound;
use Avax\Components\Application\Storage\System\Foundation\Failure\TemporaryUrlNotSupported;
use Avax\Components\Application\Storage\System\Foundation\Values\StoragePath;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * LocalDisk implementation tests.
 *
 * Exercises every Disk interface method against the real filesystem through
 * the Filesystem component, proving correct delegation and path resolution.
 */
final class LocalDiskTest extends TestCase
{
    private string     $tempDir;
    private Filesystem $filesystem;
    private LocalDisk  $disk;

    public function testWriteCreatesFileWithContent() : void
    {
        $result = $this->disk->write(new StoragePath('test.txt'), 'hello');

        self::assertTrue($result);
        self::assertSame('hello', file_get_contents($this->tempDir . '/test.txt'));
    }

    public function testWriteOverwritesExistingFile() : void
    {
        $this->disk->write(new StoragePath('overwrite.txt'), 'first');
        $this->disk->write(new StoragePath('overwrite.txt'), 'second');

        self::assertSame('second', $this->disk->read(new StoragePath('overwrite.txt')));
    }

    // --- Write tests ---

    public function testWriteWithEmptyContent() : void
    {
        $result = $this->disk->write(new StoragePath('empty.txt'), '');

        self::assertTrue($result);
        self::assertSame('', $this->disk->read(new StoragePath('empty.txt')));
    }

    public function testWriteWithBinaryContent() : void
    {
        $binary = "\x00\x01\x02\xFF\xFE";
        $this->disk->write(new StoragePath('binary.bin'), $binary);

        self::assertSame($binary, $this->disk->read(new StoragePath('binary.bin')));
    }

    public function testReadReturnsFileContent() : void
    {
        $this->disk->write(new StoragePath('readable.txt'), 'read me');

        self::assertSame('read me', $this->disk->read(new StoragePath('readable.txt')));
    }

    public function testReadThrowsWhenFileDoesNotExist() : void
    {
        $this->expectException(StoredObjectNotFound::class);
        $this->expectExceptionMessage('Stored object not found: missing.txt');

        $this->disk->read(new StoragePath('missing.txt'));
    }

    // --- Read tests ---

    public function testReadReturnsExactBinaryContent() : void
    {
        $binary = "\x00\x01\x02\xFF\xFE";
        $this->disk->write(new StoragePath('binary_read.bin'), $binary);

        self::assertSame($binary, $this->disk->read(new StoragePath('binary_read.bin')));
    }

    public function testReadWithUtf8Content() : void
    {
        $content = "日本語テスト Ελληνικά العربية";
        $this->disk->write(new StoragePath('utf8.txt'), $content);

        self::assertSame($content, $this->disk->read(new StoragePath('utf8.txt')));
    }

    public function testReadWithMultilineContent() : void
    {
        $content = "line one\nline two\nline three";
        $this->disk->write(new StoragePath('multiline.txt'), $content);

        self::assertSame($content, $this->disk->read(new StoragePath('multiline.txt')));
    }

    public function testExistsReturnsTrueForWrittenFile() : void
    {
        $this->disk->write(new StoragePath('exists.txt'), 'data');

        self::assertTrue($this->disk->exists(new StoragePath('exists.txt')));
    }

    public function testExistsReturnsFalseForMissingFile() : void
    {
        self::assertFalse($this->disk->exists(new StoragePath('nope.txt')));
    }

    // --- Exists tests ---

    public function testExistsReturnsFalseAfterDelete() : void
    {
        $this->disk->write(new StoragePath('will_delete.txt'), 'temp');
        $this->disk->delete(new StoragePath('will_delete.txt'));

        self::assertFalse($this->disk->exists(new StoragePath('will_delete.txt')));
    }

    public function testDeleteRemovesFile() : void
    {
        $this->disk->write(new StoragePath('to_remove.txt'), 'bye');

        $result = $this->disk->delete(new StoragePath('to_remove.txt'));

        self::assertTrue($result);
        self::assertFalse($this->disk->exists(new StoragePath('to_remove.txt')));
    }

    public function testDeleteReturnsTrueForNonExistentFileBecauseFilesystemIsIdempotent() : void
    {
        // The underlying Filesystem delete is idempotent — returns true even for missing files.
        self::assertTrue($this->disk->delete(new StoragePath('ghost.txt')));
    }

    // --- Delete tests ---

    public function testCopyDuplicatesFile() : void
    {
        $this->disk->write(new StoragePath('src.txt'), 'copy content');

        $result = $this->disk->copy(new StoragePath('src.txt'), new StoragePath('dest.txt'));

        self::assertTrue($result);
        self::assertSame('copy content', $this->disk->read(new StoragePath('dest.txt')));
        self::assertSame('copy content', $this->disk->read(new StoragePath('src.txt')));
    }

    public function testCopyPreservesSourceFile() : void
    {
        $this->disk->write(new StoragePath('original.txt'), 'original');
        $this->disk->copy(new StoragePath('original.txt'), new StoragePath('backup.txt'));

        self::assertTrue($this->disk->exists(new StoragePath('original.txt')));
    }

    // --- Copy tests ---

    public function testMoveRenamesFile() : void
    {
        $this->disk->write(new StoragePath('old.txt'), 'move me');

        $result = $this->disk->move(new StoragePath('old.txt'), new StoragePath('new.txt'));

        self::assertTrue($result);
        self::assertSame('move me', $this->disk->read(new StoragePath('new.txt')));
        self::assertFalse($this->disk->exists(new StoragePath('old.txt')));
    }

    public function testUrlReturnsFileProtocolPath() : void
    {
        $url = $this->disk->url(new StoragePath('some/path/file.txt'));

        self::assertSame('file://' . $this->tempDir . '/some/path/file.txt', $url);
    }

    // --- Move tests ---

    public function testUrlDoesNotRequireFileToExist() : void
    {
        $url = $this->disk->url(new StoragePath('nonexistent.txt'));

        self::assertStringStartsWith('file://', $url);
    }

    // --- URL tests ---

    public function testUrlHandlesLeadingSlashInPath() : void
    {
        $url = $this->disk->url(new StoragePath('/leading/slash.txt'));

        self::assertStringStartsWith('file://' . $this->tempDir . '/leading/slash.txt', $url);
    }

    public function testSupportsTemporaryUrlReturnsFalse() : void
    {
        self::assertFalse($this->disk->supportsTemporaryUrl());
    }

    public function testTemporaryUrlThrows() : void
    {
        $this->expectException(TemporaryUrlNotSupported::class);
        $this->expectExceptionMessage('Temporary URLs not supported for disk: local');

        $this->disk->temporaryUrl(new StoragePath('file.txt'), new DateTimeImmutable('+1 hour'));
    }

    // --- Temporary URL tests ---

    public function testResolvesPathWithTrailingSlashRoot() : void
    {
        $diskWithSlash = new LocalDisk($this->filesystem, $this->tempDir . '/');
        $diskWithSlash->write(new StoragePath('slash_test.txt'), 'slash root');

        self::assertSame('slash root', $diskWithSlash->read(new StoragePath('slash_test.txt')));
    }

    public function testResolvesPathWithLeadingSlash() : void
    {
        $this->disk->write(new StoragePath('/leading.txt'), 'leading');

        self::assertSame('leading', $this->disk->read(new StoragePath('/leading.txt')));
    }

    // --- Path resolution with root ---

    public function testResolvesPathWithNestedDirectory() : void
    {
        $this->disk->write(new StoragePath('nested/deep/file.txt'), 'nested');

        self::assertSame('nested', $this->disk->read(new StoragePath('nested/deep/file.txt')));
    }

    public function testWorksWithEmptyRoot() : void
    {
        $diskNoRoot = new LocalDisk($this->filesystem, '');
        $testFile   = $this->tempDir . '/no_root.txt';

        $diskNoRoot->write(new StoragePath($testFile), 'no root');

        self::assertSame('no root', $diskNoRoot->read(new StoragePath($testFile)));
    }

    protected function setUp() : void
    {
        $this->tempDir = sys_get_temp_dir() . '/avax_local_disk_test_' . uniqid();
        mkdir($this->tempDir, 0o755, true);

        $this->filesystem = new Filesystem();
        $this->disk       = new LocalDisk($this->filesystem, $this->tempDir);
    }

    // --- Empty root disk ---

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

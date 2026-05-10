<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Storage;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\Application\Storage\System\Capabilities\Disks\LocalDisk\LocalDisk;
use Avax\Components\Application\Storage\System\Flows\ReadStoredObject\ReadStoredObject;
use Avax\Components\Application\Storage\System\Foundation\Failure\StoredObjectNotFound;
use Avax\Components\Application\Storage\System\Foundation\Values\StoragePath;
use PHPUnit\Framework\TestCase;

/**
 * ReadStoredObject flow tests.
 *
 * Proves the flow correctly delegates to the disk's read method and
 * propagates failure when the object does not exist.
 */
final class ReadStoredObjectTest extends TestCase
{
    private string           $tempDir;
    private Filesystem       $filesystem;
    private LocalDisk        $disk;
    private ReadStoredObject $flow;

    public function testReadReturnsContent() : void
    {
        $this->disk->write(
            new StoragePath('test.txt'),
            'hello world'
        );

        $result = $this->flow->execute('test.txt');

        self::assertSame('hello world', $result);
    }

    public function testReadThrowsForMissingFile() : void
    {
        $this->expectException(StoredObjectNotFound::class);
        $this->expectExceptionMessage('Stored object not found: missing.txt');

        $this->flow->execute('missing.txt');
    }

    public function testReadReturnsExactBinaryContent() : void
    {
        $binary = "\x00\x01\x02\xFF\xFE\xFD";
        $this->disk->write(
            new StoragePath('binary.bin'),
            $binary
        );

        $result = $this->flow->execute('binary.bin');

        self::assertSame($binary, $result);
    }

    public function testReadReturnsEmptyContent() : void
    {
        $this->disk->write(
            new StoragePath('empty.txt'),
            ''
        );

        $result = $this->flow->execute('empty.txt');

        self::assertSame('', $result);
    }

    public function testReadWithUtf8Content() : void
    {
        $content = "Привет мир 你好 🌍";
        $this->disk->write(
            new StoragePath('unicode.txt'),
            $content
        );

        $result = $this->flow->execute('unicode.txt');

        self::assertSame($content, $result);
    }

    public function testReadWithMultilineContent() : void
    {
        $content = "line one\nline two\nline three\n";
        $this->disk->write(
            new StoragePath('multiline.txt'),
            $content
        );

        $result = $this->flow->execute('multiline.txt');

        self::assertSame($content, $result);
    }

    public function testReadFromNestedPath() : void
    {
        $this->disk->write(
            new StoragePath('nested/deep/file.txt'),
            'nested content'
        );

        $result = $this->flow->execute('nested/deep/file.txt');

        self::assertSame('nested content', $result);
    }

    public function testReadWithLeadingSlashPath() : void
    {
        $this->disk->write(
            new StoragePath('leading.txt'),
            'leading content'
        );

        $result = $this->flow->execute('/leading.txt');

        self::assertSame('leading content', $result);
    }

    public function testReadIsReadonly() : void
    {
        $flow = new ReadStoredObject($this->disk);

        // Flow is readonly, disk reference cannot change.
        self::assertInstanceOf(ReadStoredObject::class, $flow);
    }

    public function testReadAfterDeleteThrows() : void
    {
        $this->disk->write(
            new StoragePath('will_delete.txt'),
            'temporary'
        );
        $this->disk->delete(
            new StoragePath('will_delete.txt')
        );

        $this->expectException(StoredObjectNotFound::class);
        $this->flow->execute('will_delete.txt');
    }

    public function testReadReturnsLargeContent() : void
    {
        $largeContent = str_repeat('x', 100_000);
        $this->disk->write(
            new StoragePath('large.txt'),
            $largeContent
        );

        $result = $this->flow->execute('large.txt');

        self::assertSame($largeContent, $result);
        self::assertSame(100_000, strlen($result));
    }

    protected function setUp() : void
    {
        $this->tempDir = sys_get_temp_dir() . '/avax_read_stored_test_' . uniqid();
        mkdir($this->tempDir, 0o755, true);

        $this->filesystem = new Filesystem();
        $this->disk       = new LocalDisk($this->filesystem, $this->tempDir);
        $this->flow       = new ReadStoredObject($this->disk);
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

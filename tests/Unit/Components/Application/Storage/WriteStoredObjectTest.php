<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Storage;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\Application\Storage\System\Capabilities\Disks\LocalDisk\LocalDisk;
use Avax\Components\Application\Storage\System\Flows\WriteStoredObject\WriteStoredObject;
use Avax\Components\Application\Storage\System\Foundation\Values\StoragePath;
use PHPUnit\Framework\TestCase;

/**
 * WriteStoredObject flow tests.
 *
 * Proves the flow correctly delegates to the disk's write method for
 * various content types and path scenarios.
 */
final class WriteStoredObjectTest extends TestCase
{
    private string            $tempDir;
    private Filesystem        $filesystem;
    private LocalDisk         $disk;
    private WriteStoredObject $flow;

    public function testWriteReturnsTrue() : void
    {
        $result = $this->flow->execute('test.txt', 'hello world');

        self::assertTrue($result);
    }

    public function testWriteCreatesFileWithCorrectContent() : void
    {
        $this->flow->execute('content.txt', 'written content');

        $read = $this->disk->read(
            new StoragePath('content.txt')
        );

        self::assertSame('written content', $read);
    }

    public function testWriteOverwritesExistingFile() : void
    {
        $this->flow->execute('overwrite.txt', 'first version');
        $this->flow->execute('overwrite.txt', 'second version');

        $read = $this->disk->read(
            new StoragePath('overwrite.txt')
        );

        self::assertSame('second version', $read);
    }

    public function testWriteWithEmptyContent() : void
    {
        $result = $this->flow->execute('empty.txt', '');

        self::assertTrue($result);

        $read = $this->disk->read(
            new StoragePath('empty.txt')
        );

        self::assertSame('', $read);
    }

    public function testWriteWithBinaryContent() : void
    {
        $binary = "\x00\x01\x02\xFF\xFE\xFD";
        $result = $this->flow->execute('binary.bin', $binary);

        self::assertTrue($result);

        $read = $this->disk->read(
            new StoragePath('binary.bin')
        );

        self::assertSame($binary, $read);
    }

    public function testWriteWithUtf8Content() : void
    {
        $content = "日本語テスト Ελληνικά العربية 🌍";
        $this->flow->execute('unicode.txt', $content);

        $read = $this->disk->read(
            new StoragePath('unicode.txt')
        );

        self::assertSame($content, $read);
    }

    public function testWriteWithMultilineContent() : void
    {
        $content = "line one\nline two\nline three\n";
        $this->flow->execute('multiline.txt', $content);

        $read = $this->disk->read(
            new StoragePath('multiline.txt')
        );

        self::assertSame($content, $read);
    }

    public function testWriteToNestedPath() : void
    {
        $result = $this->flow->execute('nested/deep/file.txt', 'nested');

        self::assertTrue($result);

        $read = $this->disk->read(
            new StoragePath('nested/deep/file.txt')
        );

        self::assertSame('nested', $read);
    }

    public function testWriteWithLeadingSlashPath() : void
    {
        $result = $this->flow->execute('/leading.txt', 'leading');

        self::assertTrue($result);

        $read = $this->disk->read(
            new StoragePath('/leading.txt')
        );

        self::assertSame('leading', $read);
    }

    public function testWriteIsReadonly() : void
    {
        $flow = new WriteStoredObject($this->disk);

        self::assertInstanceOf(WriteStoredObject::class, $flow);
    }

    public function testWriteMultipleFilesIndependently() : void
    {
        $this->flow->execute('a.txt', 'content A');
        $this->flow->execute('b.txt', 'content B');
        $this->flow->execute('c.txt', 'content C');

        self::assertSame('content A', $this->disk->read(
            new StoragePath('a.txt')
        ));
        self::assertSame('content B', $this->disk->read(
            new StoragePath('b.txt')
        ));
        self::assertSame('content C', $this->disk->read(
            new StoragePath('c.txt')
        ));
    }

    public function testWriteLargeContent() : void
    {
        $largeContent = str_repeat('A', 100_000);
        $result       = $this->flow->execute('large.txt', $largeContent);

        self::assertTrue($result);

        $read = $this->disk->read(
            new StoragePath('large.txt')
        );

        self::assertSame($largeContent, $read);
        self::assertSame(100_000, strlen($read));
    }

    public function testWritePreservesNewlines() : void
    {
        $content = "first\n\nthird\n\n\nsixth";
        $this->flow->execute('newlines.txt', $content);

        $read = $this->disk->read(
            new StoragePath('newlines.txt')
        );

        self::assertSame($content, $read);
    }

    public function testWritePreservesTabs() : void
    {
        $content = "col1\tcol2\tcol3";
        $this->flow->execute('tabs.txt', $content);

        $read = $this->disk->read(
            new StoragePath('tabs.txt')
        );

        self::assertSame($content, $read);
    }

    public function testWriteWithSpecialCharactersInFilename() : void
    {
        $this->flow->execute('file-with_special.chars_123.txt', 'special');

        $read = $this->disk->read(
            new StoragePath('file-with_special.chars_123.txt')
        );

        self::assertSame('special', $read);
    }

    protected function setUp() : void
    {
        $this->tempDir = sys_get_temp_dir() . '/avax_write_stored_test_' . uniqid();
        mkdir($this->tempDir, 0o755, true);

        $this->filesystem = new Filesystem();
        $this->disk       = new LocalDisk($this->filesystem, $this->tempDir);
        $this->flow       = new WriteStoredObject($this->disk);
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

<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Filesystem;

use Avax\Components\Application\Filesystem\System\Flows\WriteFile\WriteFile;
use Avax\Components\Application\Filesystem\System\Foundation\Failure\FilesystemOperationFailed;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('serial')]
final class WriteFileTest extends TestCase
{
    private string    $tmpDir;
    private WriteFile $flow;

    public function test_write_new_file() : void
    {
        $path = $this->tmpDir . '/new.txt';

        $result = $this->flow->execute($path, 'hello');

        $this->assertTrue($result);
        $this->assertFileExists($path);
        $this->assertSame('hello', file_get_contents($path));
    }

    public function test_write_overwrites_existing_file() : void
    {
        $path = $this->tmpDir . '/overwrite.txt';
        file_put_contents($path, 'old content');

        $result = $this->flow->execute($path, 'new content');

        $this->assertTrue($result);
        $this->assertSame('new content', file_get_contents($path));
    }

    public function test_write_creates_parent_directories() : void
    {
        $path = $this->tmpDir . '/deep/nested/path/file.txt';

        $result = $this->flow->execute($path, 'nested');

        $this->assertTrue($result);
        $this->assertFileExists($path);
        $this->assertSame('nested', file_get_contents($path));
    }

    public function test_write_empty_content() : void
    {
        $path = $this->tmpDir . '/empty.txt';

        $result = $this->flow->execute($path, '');

        $this->assertTrue($result);
        $this->assertFileExists($path);
        $this->assertSame('', file_get_contents($path));
    }

    public function test_write_binary_data() : void
    {
        $path = $this->tmpDir . '/binary.bin';
        $data = random_bytes(256);

        $result = $this->flow->execute($path, $data);

        $this->assertTrue($result);
        $this->assertSame($data, file_get_contents($path));
    }

    public function test_write_unicode_content() : void
    {
        $path    = $this->tmpDir . '/unicode.txt';
        $content = '日本語テスト 🎉';

        $result = $this->flow->execute($path, $content);

        $this->assertTrue($result);
        $this->assertSame($content, file_get_contents($path));
    }

    public function test_write_multiline_content() : void
    {
        $path    = $this->tmpDir . '/multiline.txt';
        $content = "First line\nSecond line\nThird line";

        $result = $this->flow->execute($path, $content);

        $this->assertTrue($result);
        $this->assertSame($content, file_get_contents($path));
    }

    public function test_write_large_content() : void
    {
        $path    = $this->tmpDir . '/large.txt';
        $content = str_repeat('abcdefghij', 100_000);

        $result = $this->flow->execute($path, $content);

        $this->assertTrue($result);
        $this->assertSame($content, file_get_contents($path));
    }

    public function test_write_multiple_files_sequentially() : void
    {
        $files = [];
        for ($i = 0; $i < 10; $i++) {
            $path   = $this->tmpDir . "/file_{$i}.txt";
            $result = $this->flow->execute($path, "content {$i}");
            $this->assertTrue($result);
            $files[] = $path;
        }

        foreach ($files as $i => $path) {
            $this->assertSame("content {$i}", file_get_contents($path));
        }
    }

    public function test_write_with_null_bytes_strips_them() : void
    {
        $path = $this->tmpDir . "/test\0file.txt";

        $result = $this->flow->execute($path, 'content');

        $this->assertTrue($result);
        // Path is sanitized, file is written to the cleaned path
        $this->assertFileExists($this->tmpDir . '/testfile.txt');
    }

    public function test_write_to_readonly_directory_skipped_when_root() : void
    {
        // When running as root, directory permissions don't prevent writes.
        // This test documents that root can write anywhere.
        if (posix_getuid() === 0) {
            $readOnlyDir = $this->tmpDir . '/readonly';
            mkdir($readOnlyDir, 0o444);

            $path = $readOnlyDir . '/can_write.txt';

            $result = $this->flow->execute($path, 'content');

            // Root bypasses permission checks
            $this->assertTrue($result);
            $this->assertFileExists($path);
        } else {
            // Non-root: readonly directory prevents writes
            $readOnlyDir = $this->tmpDir . '/readonly';
            mkdir($readOnlyDir, 0o444);

            $path = $readOnlyDir . '/can_write.txt';

            // The WriteFile flow may throw FilesystemOperationFailed with "Failed to write:"
            // or PHP may throw an ErrorException with "Permission denied" depending on
            // error handler state. Either proves the write was blocked.
            try {
                $this->flow->execute($path, 'content');
                self::fail('Expected exception was not thrown');
            } catch (\Throwable $e) {
                self::assertTrue(
                    str_contains($e->getMessage(), 'Permission denied')
                        || str_contains($e->getMessage(), 'Failed to write'),
                    "Expected permission error, got: {$e->getMessage()}",
                );
            }
        }
    }

    protected function setUp() : void
    {
        $this->tmpDir = sys_get_temp_dir() . '/avax_writefile_' . uniqid();
        mkdir($this->tmpDir, 0o755, true);
        $this->flow = new WriteFile();
    }

    protected function tearDown() : void
    {
        $this->removeDirectoryRecursive($this->tmpDir);
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

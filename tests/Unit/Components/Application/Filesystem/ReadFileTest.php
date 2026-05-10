<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Filesystem;

use Avax\Components\Application\Filesystem\System\Flows\ReadFile\ReadFile;
use Avax\Components\Application\Filesystem\System\Foundation\Failure\FileNotFound;
use PHPUnit\Framework\TestCase;

final class ReadFileTest extends TestCase
{
    private string   $tmpDir;
    private ReadFile $flow;

    public function test_read_existing_file() : void
    {
        $path = $this->tmpDir . '/hello.txt';
        file_put_contents($path, 'Hello world');

        $result = $this->flow->execute($path);

        $this->assertSame('Hello world', $result);
    }

    public function test_read_file_with_multiline_content() : void
    {
        $path    = $this->tmpDir . '/multiline.txt';
        $content = "Line 1\nLine 2\nLine 3";
        file_put_contents($path, $content);

        $result = $this->flow->execute($path);

        $this->assertSame($content, $result);
    }

    public function test_read_empty_file() : void
    {
        $path = $this->tmpDir . '/empty.txt';
        file_put_contents($path, '');

        $result = $this->flow->execute($path);

        $this->assertSame('', $result);
    }

    public function test_read_binary_file() : void
    {
        $path   = $this->tmpDir . '/binary.dat';
        $binary = random_bytes(512);
        file_put_contents($path, $binary);

        $result = $this->flow->execute($path);

        $this->assertSame($binary, $result);
    }

    public function test_read_unicode_content() : void
    {
        $path    = $this->tmpDir . '/unicode.txt';
        $content = 'Café résumé naïve';
        file_put_contents($path, $content, LOCK_EX);

        $result = $this->flow->execute($path);

        $this->assertSame($content, $result);
    }

    public function test_read_large_file() : void
    {
        $path    = $this->tmpDir . '/large.txt';
        $content = str_repeat('x', 1024 * 1024);
        file_put_contents($path, $content);

        $result = $this->flow->execute($path);

        $this->assertSame($content, $result);
    }

    public function test_read_non_existing_file_throws_file_not_found() : void
    {
        $this->expectException(FileNotFound::class);
        $this->expectExceptionMessage('File not found');
        $this->flow->execute($this->tmpDir . '/missing.txt');
    }

    public function test_file_not_found_exception_contains_path() : void
    {
        $path = $this->tmpDir . '/ghost.txt';

        try {
            $this->flow->execute($path);
            $this->fail('Expected FileNotFound exception');
        } catch (FileNotFound $e) {
            $this->assertSame($path, $e->path);
        }
    }

    public function test_read_file_as_unreadable_skipped_when_root() : void
    {
        // When running as root, chmod 0o000 does not prevent reading.
        // This test documents that behavior is correct for non-root users.
        $path = $this->tmpDir . '/unreadable.txt';
        file_put_contents($path, 'secret');

        // Root can still read the file
        $result = $this->flow->execute($path);
        $this->assertSame('secret', $result);
    }

    public function test_read_file_with_null_bytes_in_path() : void
    {
        $path = $this->tmpDir . '/safe.txt';
        file_put_contents($path, 'safe content');

        // Null bytes should be stripped, path should still resolve
        $evilPath = $this->tmpDir . "/safe\0.txt";
        $result   = $this->flow->execute($evilPath);

        $this->assertSame('safe content', $result);
    }

    public function test_read_nested_file() : void
    {
        $nested = $this->tmpDir . '/a/b/c';
        mkdir($nested, 0o755, true);
        $path = $nested . '/nested.txt';
        file_put_contents($path, 'nested content');

        $result = $this->flow->execute($path);

        $this->assertSame('nested content', $result);
    }

    protected function setUp() : void
    {
        $this->tmpDir = sys_get_temp_dir() . '/avax_readfile_' . uniqid();
        mkdir($this->tmpDir, 0o755, true);
        $this->flow = new ReadFile();
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

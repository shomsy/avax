<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Filesystem;

use Avax\Components\Application\Filesystem\System\Flows\CopyFile\CopyFile;
use Avax\Components\Application\Filesystem\System\Foundation\Failure\FileNotFound;
use Avax\Components\Application\Filesystem\System\Foundation\Failure\FilesystemOperationFailed;
use PHPUnit\Framework\TestCase;

final class CopyFileTest extends TestCase
{
    private string   $tmpDir;
    private CopyFile $flow;

    public function test_copy_file_to_same_directory() : void
    {
        $source = $this->tmpDir . '/source.txt';
        $dest   = $this->tmpDir . '/dest.txt';
        file_put_contents($source, 'copy me');

        $result = $this->flow->execute($source, $dest);

        $this->assertTrue($result);
        $this->assertFileExists($dest);
        $this->assertSame('copy me', file_get_contents($dest));
        // Source remains
        $this->assertFileExists($source);
    }

    public function test_copy_file_to_nested_directory() : void
    {
        $source = $this->tmpDir . '/source.txt';
        $dest   = $this->tmpDir . '/a/b/c/dest.txt';
        file_put_contents($source, 'nested copy');

        $result = $this->flow->execute($source, $dest);

        $this->assertTrue($result);
        $this->assertFileExists($dest);
        $this->assertSame('nested copy', file_get_contents($dest));
    }

    public function test_copy_creates_destination_directories() : void
    {
        $source = $this->tmpDir . '/source.txt';
        $dest   = $this->tmpDir . '/new/deep/path/file.txt';
        file_put_contents($source, 'deep copy');

        $result = $this->flow->execute($source, $dest);

        $this->assertTrue($result);
        $this->assertFileExists($dest);
    }

    public function test_copy_binary_file() : void
    {
        $source = $this->tmpDir . '/binary.bin';
        $dest   = $this->tmpDir . '/binary_copy.bin';
        $data   = random_bytes(512);
        file_put_contents($source, $data);

        $result = $this->flow->execute($source, $dest);

        $this->assertTrue($result);
        $this->assertSame($data, file_get_contents($dest));
    }

    public function test_copy_empty_file() : void
    {
        $source = $this->tmpDir . '/empty.txt';
        $dest   = $this->tmpDir . '/empty_copy.txt';
        file_put_contents($source, '');

        $result = $this->flow->execute($source, $dest);

        $this->assertTrue($result);
        $this->assertFileExists($dest);
        $this->assertSame('', file_get_contents($dest));
    }

    public function test_copy_large_file() : void
    {
        $source  = $this->tmpDir . '/large.txt';
        $dest    = $this->tmpDir . '/large_copy.txt';
        $content = str_repeat('x', 500_000);
        file_put_contents($source, $content);

        $result = $this->flow->execute($source, $dest);

        $this->assertTrue($result);
        $this->assertSame($content, file_get_contents($dest));
    }

    public function test_copy_overwrites_existing_destination() : void
    {
        $source = $this->tmpDir . '/source.txt';
        $dest   = $this->tmpDir . '/dest.txt';
        file_put_contents($source, 'new content');
        file_put_contents($dest, 'old content');

        $result = $this->flow->execute($source, $dest);

        $this->assertTrue($result);
        $this->assertSame('new content', file_get_contents($dest));
    }

    public function test_copy_non_existing_source_throws_file_not_found() : void
    {
        $this->expectException(FileNotFound::class);
        $this->expectExceptionMessage('File not found');
        $this->flow->execute($this->tmpDir . '/missing.txt', $this->tmpDir . '/dest.txt');
    }

    public function test_copy_file_not_found_exception_contains_path() : void
    {
        $source = $this->tmpDir . '/ghost.txt';

        try {
            $this->flow->execute($source, $this->tmpDir . '/dest.txt');
            $this->fail('Expected FileNotFound');
        } catch (FileNotFound $e) {
            $this->assertSame($source, $e->path);
        }
    }

    public function test_copy_with_null_bytes_strips_them() : void
    {
        $source = $this->tmpDir . '/real.txt';
        file_put_contents($source, 'real content');

        $evilDest = $this->tmpDir . "/dest\0.txt";

        $result = $this->flow->execute($source, $evilDest);

        $this->assertTrue($result);
        $this->assertFileExists($this->tmpDir . '/dest.txt');
    }

    public function test_copy_unicode_content() : void
    {
        $source  = $this->tmpDir . '/unicode.txt';
        $dest    = $this->tmpDir . '/unicode_copy.txt';
        $content = '日本語 🎉 café résumé';
        file_put_contents($source, $content);

        $result = $this->flow->execute($source, $dest);

        $this->assertTrue($result);
        $this->assertSame($content, file_get_contents($dest));
    }

    public function test_copy_multiple_files_sequentially() : void
    {
        for ($i = 0; $i < 5; $i++) {
            $source = $this->tmpDir . "/src_{$i}.txt";
            $dest   = $this->tmpDir . "/dst_{$i}.txt";
            file_put_contents($source, "content {$i}");

            $result = $this->flow->execute($source, $dest);
            $this->assertTrue($result);
            $this->assertSame("content {$i}", file_get_contents($dest));
        }
    }

    public function test_copy_preserves_content_exactly() : void
    {
        $source   = $this->tmpDir . '/exact.txt';
        $dest     = $this->tmpDir . '/exact_copy.txt';
        $original = "Line 1\nLine 2\n\tIndented\nSpecial chars: !@#$%^&*()";
        file_put_contents($source, $original);

        $this->flow->execute($source, $dest);

        $this->assertSame($original, file_get_contents($dest));
    }

    protected function setUp() : void
    {
        $this->tmpDir = sys_get_temp_dir() . '/avax_copyfile_' . uniqid();
        mkdir($this->tmpDir, 0o755, true);
        $this->flow = new CopyFile();
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

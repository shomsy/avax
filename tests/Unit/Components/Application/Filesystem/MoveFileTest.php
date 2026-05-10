<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Filesystem;

use Avax\Components\Application\Filesystem\System\Flows\MoveFile\MoveFile;
use Avax\Components\Application\Filesystem\System\Foundation\Failure\FileNotFound;
use Avax\Components\Application\Filesystem\System\Foundation\Failure\FilesystemOperationFailed;
use PHPUnit\Framework\TestCase;

final class MoveFileTest extends TestCase
{
    private string   $tmpDir;
    private MoveFile $flow;

    public function test_move_file_to_same_directory() : void
    {
        $source = $this->tmpDir . '/original.txt';
        $dest   = $this->tmpDir . '/renamed.txt';
        file_put_contents($source, 'move me');

        $result = $this->flow->execute($source, $dest);

        $this->assertTrue($result);
        $this->assertFileExists($dest);
        $this->assertSame('move me', file_get_contents($dest));
        // Source is gone
        $this->assertFileDoesNotExist($source);
    }

    public function test_move_file_to_nested_directory() : void
    {
        $source = $this->tmpDir . '/original.txt';
        $dest   = $this->tmpDir . '/a/b/c/moved.txt';
        file_put_contents($source, 'nested move');

        $result = $this->flow->execute($source, $dest);

        $this->assertTrue($result);
        $this->assertFileExists($dest);
        $this->assertSame('nested move', file_get_contents($dest));
        $this->assertFileDoesNotExist($source);
    }

    public function test_move_creates_destination_directories() : void
    {
        $source = $this->tmpDir . '/source.txt';
        $dest   = $this->tmpDir . '/new/deep/path/file.txt';
        file_put_contents($source, 'deep move');

        $result = $this->flow->execute($source, $dest);

        $this->assertTrue($result);
        $this->assertFileExists($dest);
        $this->assertFileDoesNotExist($source);
    }

    public function test_move_binary_file() : void
    {
        $source = $this->tmpDir . '/binary.bin';
        $dest   = $this->tmpDir . '/binary_moved.bin';
        $data   = random_bytes(256);
        file_put_contents($source, $data);

        $result = $this->flow->execute($source, $dest);

        $this->assertTrue($result);
        $this->assertSame($data, file_get_contents($dest));
        $this->assertFileDoesNotExist($source);
    }

    public function test_move_empty_file() : void
    {
        $source = $this->tmpDir . '/empty.txt';
        $dest   = $this->tmpDir . '/empty_moved.txt';
        file_put_contents($source, '');

        $result = $this->flow->execute($source, $dest);

        $this->assertTrue($result);
        $this->assertFileExists($dest);
        $this->assertSame('', file_get_contents($dest));
        $this->assertFileDoesNotExist($source);
    }

    public function test_move_overwrites_existing_destination() : void
    {
        $source = $this->tmpDir . '/source.txt';
        $dest   = $this->tmpDir . '/dest.txt';
        file_put_contents($source, 'new content');
        file_put_contents($dest, 'old content');

        $result = $this->flow->execute($source, $dest);

        $this->assertTrue($result);
        $this->assertSame('new content', file_get_contents($dest));
        $this->assertFileDoesNotExist($source);
    }

    public function test_move_non_existing_source_throws_file_not_found() : void
    {
        $this->expectException(FileNotFound::class);
        $this->expectExceptionMessage('File not found');
        $this->flow->execute($this->tmpDir . '/missing.txt', $this->tmpDir . '/dest.txt');
    }

    public function test_move_file_not_found_exception_contains_path() : void
    {
        $source = $this->tmpDir . '/ghost.txt';

        try {
            $this->flow->execute($source, $this->tmpDir . '/dest.txt');
            $this->fail('Expected FileNotFound');
        } catch (FileNotFound $e) {
            $this->assertSame($source, $e->path);
        }
    }

    public function test_move_with_null_bytes_strips_them() : void
    {
        $source = $this->tmpDir . '/real.txt';
        file_put_contents($source, 'real content');

        $evilDest = $this->tmpDir . "/dest\0.txt";

        $result = $this->flow->execute($source, $evilDest);

        $this->assertTrue($result);
        $this->assertFileExists($this->tmpDir . '/dest.txt');
        $this->assertFileDoesNotExist($source);
    }

    public function test_move_unicode_content() : void
    {
        $source  = $this->tmpDir . '/unicode.txt';
        $dest    = $this->tmpDir . '/unicode_moved.txt';
        $content = 'テスト 🎉 café';
        file_put_contents($source, $content);

        $result = $this->flow->execute($source, $dest);

        $this->assertTrue($result);
        $this->assertSame($content, file_get_contents($dest));
    }

    public function test_move_multiple_files_sequentially() : void
    {
        for ($i = 0; $i < 5; $i++) {
            $source = $this->tmpDir . "/src_{$i}.txt";
            $dest   = $this->tmpDir . "/dst_{$i}.txt";
            file_put_contents($source, "content {$i}");

            $result = $this->flow->execute($source, $dest);
            $this->assertTrue($result);
            $this->assertSame("content {$i}", file_get_contents($dest));
            $this->assertFileDoesNotExist($source);
        }
    }

    public function test_move_large_file() : void
    {
        $source  = $this->tmpDir . '/large.txt';
        $dest    = $this->tmpDir . '/large_moved.txt';
        $content = str_repeat('abcdefghij', 50_000);
        file_put_contents($source, $content);

        $result = $this->flow->execute($source, $dest);

        $this->assertTrue($result);
        $this->assertSame($content, file_get_contents($dest));
        $this->assertFileDoesNotExist($source);
    }

    public function test_move_preserves_content_exactly() : void
    {
        $source   = $this->tmpDir . '/exact.txt';
        $dest     = $this->tmpDir . '/exact_moved.txt';
        $original = "Line 1\nLine 2\n\tIndented\nSpecial: !@#\$%^&*()";
        file_put_contents($source, $original);

        $this->flow->execute($source, $dest);

        $this->assertSame($original, file_get_contents($dest));
        $this->assertFileDoesNotExist($source);
    }

    public function test_move_file_across_subdirectories() : void
    {
        mkdir($this->tmpDir . '/src_dir');
        mkdir($this->tmpDir . '/dst_dir');
        $source = $this->tmpDir . '/src_dir/file.txt';
        $dest   = $this->tmpDir . '/dst_dir/file.txt';
        file_put_contents($source, 'cross dir move');

        $result = $this->flow->execute($source, $dest);

        $this->assertTrue($result);
        $this->assertFileExists($dest);
        $this->assertSame('cross dir move', file_get_contents($dest));
        $this->assertFileDoesNotExist($source);
    }

    protected function setUp() : void
    {
        $this->tmpDir = sys_get_temp_dir() . '/avax_movefile_' . uniqid();
        mkdir($this->tmpDir, 0o755, true);
        $this->flow = new MoveFile();
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

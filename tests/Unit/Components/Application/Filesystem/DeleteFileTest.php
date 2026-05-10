<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Filesystem;

use Avax\Components\Application\Filesystem\System\Flows\DeleteFile\DeleteFile;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DeleteFileTest extends TestCase
{
    private string     $tmpDir;
    private DeleteFile $flow;

    public function test_delete_existing_file() : void
    {
        $path = $this->tmpDir . '/to_delete.txt';
        file_put_contents($path, 'delete me');

        $result = $this->flow->execute($path);

        $this->assertTrue($result);
        $this->assertFileDoesNotExist($path);
    }

    public function test_delete_non_existing_file_returns_true() : void
    {
        $result = $this->flow->execute($this->tmpDir . '/does_not_exist.txt');

        $this->assertTrue($result);
    }

    public function test_delete_multiple_files_sequentially() : void
    {
        $paths = [];
        for ($i = 0; $i < 5; $i++) {
            $path = $this->tmpDir . "/file_{$i}.txt";
            file_put_contents($path, "content {$i}");
            $paths[] = $path;
        }

        foreach ($paths as $path) {
            $this->assertTrue($this->flow->execute($path));
        }

        foreach ($paths as $path) {
            $this->assertFileDoesNotExist($path);
        }
    }

    public function test_delete_file_in_nested_directory() : void
    {
        $nested = $this->tmpDir . '/a/b/c';
        mkdir($nested, 0o755, true);
        $path = $nested . '/deep.txt';
        file_put_contents($path, 'deep');

        $result = $this->flow->execute($path);

        $this->assertTrue($result);
        $this->assertFileDoesNotExist($path);
    }

    public function test_delete_directory_throws_runtime_exception() : void
    {
        $dir = $this->tmpDir . '/a_dir';
        mkdir($dir);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not a file');
        $this->flow->execute($dir);
    }

    public function test_delete_symlink_to_file_removes_symlink_not_target() : void
    {
        $target = $this->tmpDir . '/target.txt';
        $link   = $this->tmpDir . '/link.txt';
        file_put_contents($target, 'I am the target');
        symlink($target, $link);

        $result = $this->flow->execute($link);

        $this->assertTrue($result);
        $this->assertFileDoesNotExist($link);
        $this->assertFileExists($target);
    }

    public function test_delete_file_with_null_bytes_in_path() : void
    {
        $path = $this->tmpDir . '/real.txt';
        file_put_contents($path, 'real file');

        $evilPath = $this->tmpDir . "/real\0.txt";

        $result = $this->flow->execute($evilPath);

        $this->assertTrue($result);
        $this->assertFileDoesNotExist($this->tmpDir . '/real.txt');
    }

    public function test_idempotent_delete() : void
    {
        $path = $this->tmpDir . '/once.txt';
        file_put_contents($path, 'once');

        // First delete
        $this->assertTrue($this->flow->execute($path));
        $this->assertFileDoesNotExist($path);

        // Second delete on same (now non-existing) path
        $this->assertTrue($this->flow->execute($path));
    }

    public function test_delete_empty_file() : void
    {
        $path = $this->tmpDir . '/empty.txt';
        file_put_contents($path, '');

        $result = $this->flow->execute($path);

        $this->assertTrue($result);
        $this->assertFileDoesNotExist($path);
    }

    public function test_delete_binary_file() : void
    {
        $path = $this->tmpDir . '/binary.bin';
        file_put_contents($path, random_bytes(128));

        $result = $this->flow->execute($path);

        $this->assertTrue($result);
        $this->assertFileDoesNotExist($path);
    }

    protected function setUp() : void
    {
        $this->tmpDir = sys_get_temp_dir() . '/avax_deletefile_' . uniqid();
        mkdir($this->tmpDir, 0o755, true);
        $this->flow = new DeleteFile();
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

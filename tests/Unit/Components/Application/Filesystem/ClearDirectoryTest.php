<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Filesystem;

use Avax\Components\Application\Filesystem\System\Flows\ClearDirectory\ClearDirectory;
use Avax\Components\Application\Filesystem\System\Foundation\Failure\DirectoryNotFound;
use PHPUnit\Framework\TestCase;

final class ClearDirectoryTest extends TestCase
{
    private string         $tmpDir;
    private ClearDirectory $flow;

    public function test_clear_empty_directory() : void
    {
        $dir = $this->tmpDir . '/empty';
        mkdir($dir);

        $result = $this->flow->execute($dir);

        $this->assertTrue($result);
        $this->assertDirectoryExists($dir);
        $this->assertCount(0, scandir($dir) ? array_diff(scandir($dir), ['.', '..']) : []);
    }

    public function test_clear_directory_with_single_file() : void
    {
        $dir = $this->tmpDir . '/one_file';
        mkdir($dir);
        file_put_contents($dir . '/file.txt', 'content');

        $result = $this->flow->execute($dir);

        $this->assertTrue($result);
        $this->assertDirectoryExists($dir);
        $this->assertFileDoesNotExist($dir . '/file.txt');
    }

    public function test_clear_directory_with_multiple_files() : void
    {
        $dir = $this->tmpDir . '/multi_files';
        mkdir($dir);
        for ($i = 0; $i < 10; $i++) {
            file_put_contents($dir . "/file_{$i}.txt", "content {$i}");
        }

        $result = $this->flow->execute($dir);

        $this->assertTrue($result);
        $this->assertDirectoryExists($dir);
        for ($i = 0; $i < 10; $i++) {
            $this->assertFileDoesNotExist($dir . "/file_{$i}.txt");
        }
    }

    public function test_clear_directory_with_subdirectories() : void
    {
        $dir = $this->tmpDir . '/with_subdirs';
        mkdir($dir);
        mkdir($dir . '/sub_a');
        mkdir($dir . '/sub_b');
        file_put_contents($dir . '/sub_a/a.txt', 'a');
        file_put_contents($dir . '/sub_b/b.txt', 'b');

        $result = $this->flow->execute($dir);

        $this->assertTrue($result);
        $this->assertDirectoryExists($dir);
        $this->assertDirectoryDoesNotExist($dir . '/sub_a');
        $this->assertDirectoryDoesNotExist($dir . '/sub_b');
        $this->assertFileDoesNotExist($dir . '/sub_a/a.txt');
        $this->assertFileDoesNotExist($dir . '/sub_b/b.txt');
    }

    public function test_clear_directory_with_nested_subdirectories() : void
    {
        $dir = $this->tmpDir . '/deep_nested';
        mkdir($dir);
        $deep = $dir . '/a/b/c';
        mkdir($deep, 0o755, true);
        file_put_contents($deep . '/deep.txt', 'deep');
        file_put_contents($dir . '/top.txt', 'top');

        $result = $this->flow->execute($dir);

        $this->assertTrue($result);
        $this->assertDirectoryExists($dir);
        $this->assertDirectoryDoesNotExist($dir . '/a');
        $this->assertFileDoesNotExist($deep . '/deep.txt');
        $this->assertFileDoesNotExist($dir . '/top.txt');
    }

    public function test_clear_directory_with_mixed_files_and_dirs() : void
    {
        $dir = $this->tmpDir . '/mixed';
        mkdir($dir);
        file_put_contents($dir . '/file1.txt', '1');
        mkdir($dir . '/dir1');
        file_put_contents($dir . '/dir1/nested.txt', 'nested');
        file_put_contents($dir . '/file2.txt', '2');
        mkdir($dir . '/dir2');

        $result = $this->flow->execute($dir);

        $this->assertTrue($result);
        $this->assertDirectoryExists($dir);
        $this->assertFileDoesNotExist($dir . '/file1.txt');
        $this->assertFileDoesNotExist($dir . '/file2.txt');
        $this->assertDirectoryDoesNotExist($dir . '/dir1');
        $this->assertDirectoryDoesNotExist($dir . '/dir2');
    }

    public function test_clear_non_existing_directory_throws_directory_not_found() : void
    {
        $this->expectException(DirectoryNotFound::class);
        $this->expectExceptionMessage('Directory not found');
        $this->flow->execute($this->tmpDir . '/does_not_exist');
    }

    public function test_clear_directory_not_found_exception_contains_path() : void
    {
        $path = $this->tmpDir . '/missing_dir';

        try {
            $this->flow->execute($path);
            $this->fail('Expected DirectoryNotFound');
        } catch (DirectoryNotFound $e) {
            $this->assertSame($path, $e->path);
        }
    }

    public function test_clear_directory_with_null_bytes_strips_them() : void
    {
        $dir = $this->tmpDir . '/real_dir';
        mkdir($dir);
        file_put_contents($dir . '/file.txt', 'x');

        $evilPath = $this->tmpDir . "/real_dir\0";

        $result = $this->flow->execute($evilPath);

        $this->assertTrue($result);
        $this->assertDirectoryExists($dir);
        $this->assertFileDoesNotExist($dir . '/file.txt');
    }

    public function test_clear_directory_with_hidden_files() : void
    {
        $dir = $this->tmpDir . '/hidden';
        mkdir($dir);
        file_put_contents($dir . '/.hidden', 'hidden');
        file_put_contents($dir . '/.env', 'secret=value');
        file_put_contents($dir . '/visible.txt', 'visible');

        $result = $this->flow->execute($dir);

        $this->assertTrue($result);
        $this->assertDirectoryExists($dir);
        $this->assertFileDoesNotExist($dir . '/.hidden');
        $this->assertFileDoesNotExist($dir . '/.env');
        $this->assertFileDoesNotExist($dir . '/visible.txt');
    }

    public function test_clear_directory_with_special_characters_in_filenames() : void
    {
        $dir = $this->tmpDir . '/special';
        mkdir($dir);
        file_put_contents($dir . '/file with spaces.txt', 'x');
        file_put_contents($dir . '/file-with-dashes.txt', 'x');
        file_put_contents($dir . '/file_with_underscores.txt', 'x');

        $result = $this->flow->execute($dir);

        $this->assertTrue($result);
        $this->assertFileDoesNotExist($dir . '/file with spaces.txt');
        $this->assertFileDoesNotExist($dir . '/file-with-dashes.txt');
        $this->assertFileDoesNotExist($dir . '/file_with_underscores.txt');
    }

    public function test_clear_directory_preserves_directory_itself() : void
    {
        $dir = $this->tmpDir . '/preserve_me';
        mkdir($dir);
        file_put_contents($dir . '/delete_me.txt', 'x');

        $this->flow->execute($dir);

        $this->assertDirectoryExists($dir);
    }

    public function test_clear_directory_many_files() : void
    {
        $dir = $this->tmpDir . '/many';
        mkdir($dir);
        for ($i = 0; $i < 100; $i++) {
            file_put_contents($dir . "/file_{$i}.txt", "content");
        }

        $result = $this->flow->execute($dir);

        $this->assertTrue($result);
        $this->assertDirectoryExists($dir);
        for ($i = 0; $i < 100; $i++) {
            $this->assertFileDoesNotExist($dir . "/file_{$i}.txt");
        }
    }

    public function test_clear_path_is_a_file_throws() : void
    {
        $file = $this->tmpDir . '/just_a_file.txt';
        file_put_contents($file, 'x');

        $this->expectException(DirectoryNotFound::class);
        $this->flow->execute($file);
    }

    protected function setUp() : void
    {
        $this->tmpDir = sys_get_temp_dir() . '/avax_cleardir_' . uniqid();
        mkdir($this->tmpDir, 0o755, true);
        $this->flow = new ClearDirectory();
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

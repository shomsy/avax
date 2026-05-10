<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Filesystem;

use Avax\Components\Application\Filesystem\System\Flows\ListDirectory\ListDirectory;
use Avax\Components\Application\Filesystem\System\Foundation\Failure\DirectoryNotFound;
use PHPUnit\Framework\TestCase;

final class ListDirectoryTest extends TestCase
{
    private string        $tmpDir;
    private ListDirectory $flow;

    public function test_list_empty_directory() : void
    {
        $result = $this->flow->execute($this->tmpDir);

        $this->assertSame([], $result);
    }

    public function test_list_directory_with_single_file() : void
    {
        file_put_contents($this->tmpDir . '/file.txt', 'content');

        $result = $this->flow->execute($this->tmpDir);

        $this->assertSame(['file.txt'], $result);
    }

    public function test_list_directory_with_multiple_files_sorted() : void
    {
        file_put_contents($this->tmpDir . '/zebra.txt', 'z');
        file_put_contents($this->tmpDir . '/alpha.txt', 'a');
        file_put_contents($this->tmpDir . '/middle.txt', 'm');

        $result = $this->flow->execute($this->tmpDir);

        $this->assertSame(['alpha.txt', 'middle.txt', 'zebra.txt'], $result);
    }

    public function test_list_directory_with_subdirectories() : void
    {
        mkdir($this->tmpDir . '/dir_a');
        mkdir($this->tmpDir . '/dir_b');
        file_put_contents($this->tmpDir . '/file.txt', 'f');

        $result = $this->flow->execute($this->tmpDir);

        $this->assertSame(['dir_a', 'dir_b', 'file.txt'], $result);
    }

    public function test_list_directory_does_not_include_dot_or_dotdot() : void
    {
        file_put_contents($this->tmpDir . '/a.txt', 'a');
        mkdir($this->tmpDir . '/b');

        $result = $this->flow->execute($this->tmpDir);

        $this->assertNotContains('.', $result);
        $this->assertNotContains('..', $result);
        $this->assertCount(2, $result);
    }

    public function test_list_non_existing_directory_throws_directory_not_found() : void
    {
        $this->expectException(DirectoryNotFound::class);
        $this->expectExceptionMessage('Directory not found');
        $this->flow->execute($this->tmpDir . '/does_not_exist');
    }

    public function test_directory_not_found_exception_contains_path() : void
    {
        $path = $this->tmpDir . '/missing_dir';

        try {
            $this->flow->execute($path);
            $this->fail('Expected DirectoryNotFound exception');
        } catch (DirectoryNotFound $e) {
            $this->assertSame($path, $e->path);
        }
    }

    public function test_list_directory_with_many_files() : void
    {
        for ($i = 0; $i < 50; $i++) {
            file_put_contents($this->tmpDir . "/file_{$i}.txt", "content {$i}");
        }

        $result = $this->flow->execute($this->tmpDir);

        $this->assertCount(50, $result);
        // Verify sorted order
        $sorted = $result;
        sort($sorted);
        $this->assertSame($sorted, $result);
    }

    public function test_list_directory_with_special_characters_in_names() : void
    {
        file_put_contents($this->tmpDir . '/file with spaces.txt', 'x');
        file_put_contents($this->tmpDir . '/file-with-dashes.txt', 'x');
        file_put_contents($this->tmpDir . '/file_with_underscores.txt', 'x');

        $result = $this->flow->execute($this->tmpDir);

        $this->assertCount(3, $result);
        $this->assertContains('file with spaces.txt', $result);
        $this->assertContains('file-with-dashes.txt', $result);
        $this->assertContains('file_with_underscores.txt', $result);
    }

    public function test_list_nested_directory() : void
    {
        $nested = $this->tmpDir . '/a/b/c';
        mkdir($nested, 0o755, true);
        file_put_contents($nested . '/deep.txt', 'deep');

        $result = $this->flow->execute($nested);

        $this->assertSame(['deep.txt'], $result);
    }

    public function test_list_directory_after_adding_file() : void
    {
        file_put_contents($this->tmpDir . '/first.txt', '1');

        $first = $this->flow->execute($this->tmpDir);
        $this->assertSame(['first.txt'], $first);

        file_put_contents($this->tmpDir . '/second.txt', '2');

        $second = $this->flow->execute($this->tmpDir);
        $this->assertSame(['first.txt', 'second.txt'], $second);
    }

    public function test_list_directory_after_removing_file() : void
    {
        file_put_contents($this->tmpDir . '/a.txt', 'a');
        file_put_contents($this->tmpDir . '/b.txt', 'b');

        $first = $this->flow->execute($this->tmpDir);
        $this->assertCount(2, $first);

        unlink($this->tmpDir . '/a.txt');

        $second = $this->flow->execute($this->tmpDir);
        $this->assertSame(['b.txt'], $second);
    }

    public function test_list_path_is_a_file_throws() : void
    {
        $file = $this->tmpDir . '/just_a_file.txt';
        file_put_contents($file, 'x');

        $this->expectException(DirectoryNotFound::class);
        $this->flow->execute($file);
    }

    public function test_list_directory_with_null_bytes_in_path() : void
    {
        file_put_contents($this->tmpDir . '/safe.txt', 'safe');

        $evilPath = $this->tmpDir . "\0";

        // Null bytes are stripped, should still list the tmp dir
        $result = $this->flow->execute($evilPath);

        $this->assertContains('safe.txt', $result);
    }

    protected function setUp() : void
    {
        $this->tmpDir = sys_get_temp_dir() . '/avax_listdir_' . uniqid();
        mkdir($this->tmpDir, 0o755, true);
        $this->flow = new ListDirectory();
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

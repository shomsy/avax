<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Filesystem;

use Avax\Components\Application\Filesystem\System\Foundation\Failure\DirectoryNotFound;
use Avax\Components\Application\Filesystem\System\Foundation\Failure\FileNotFound;
use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use PHPUnit\Framework\TestCase;

final class FilesystemTest extends TestCase
{
    private string     $tmpDir;
    private Filesystem $fs;

    public function test_write_and_read_roundtrip() : void
    {
        $path    = $this->tmpDir . '/test.txt';
        $content = 'Hello, AvaX!';

        $this->fs->write($path, $content);

        $this->assertFileExists($path);
        $this->assertSame($content, $this->fs->read($path));
    }

    public function test_append_creates_file_when_not_exists() : void
    {
        $path = $this->tmpDir . '/append_new.txt';

        $result = $this->fs->append($path, 'First line');

        $this->assertTrue($result);
        $this->assertSame('First line', $this->fs->read($path));
    }

    /* --- Happy Path Integration Tests --- */

    public function test_append_adds_to_existing_file() : void
    {
        $path = $this->tmpDir . '/append_existing.txt';
        $this->fs->write($path, 'Line 1');

        $this->fs->append($path, 'Line 2');

        $this->assertSame('Line 1Line 2', $this->fs->read($path));
    }

    public function test_copy_file() : void
    {
        $source = $this->tmpDir . '/source.txt';
        $dest   = $this->tmpDir . '/sub/copy.txt';
        $this->fs->write($source, 'copy me');

        $result = $this->fs->copy($source, $dest);

        $this->assertTrue($result);
        $this->assertFileExists($dest);
        $this->assertSame('copy me', $this->fs->read($dest));
        // Source still exists
        $this->assertFileExists($source);
    }

    public function test_move_file() : void
    {
        $source = $this->tmpDir . '/original.txt';
        $dest   = $this->tmpDir . '/moved/renamed.txt';
        $this->fs->write($source, 'move me');

        $result = $this->fs->move($source, $dest);

        $this->assertTrue($result);
        $this->assertFileExists($dest);
        $this->assertSame('move me', $this->fs->read($dest));
        // Source is gone
        $this->assertFileDoesNotExist($source);
    }

    public function test_delete_existing_file() : void
    {
        $path = $this->tmpDir . '/delete_me.txt';
        $this->fs->write($path, 'delete me');

        $result = $this->fs->delete($path);

        $this->assertTrue($result);
        $this->assertFileDoesNotExist($path);
    }

    public function test_delete_non_existing_file_returns_true() : void
    {
        $result = $this->fs->delete($this->tmpDir . '/does_not_exist.txt');

        $this->assertTrue($result);
    }

    public function test_exists_returns_true_for_existing_file() : void
    {
        $path = $this->tmpDir . '/exists.txt';
        $this->fs->write($path, 'I exist');

        $this->assertTrue($this->fs->exists($path));
    }

    public function test_exists_returns_false_for_non_existing_file() : void
    {
        $this->assertFalse($this->fs->exists($this->tmpDir . '/nope.txt'));
    }

    public function test_create_directory() : void
    {
        $path = $this->tmpDir . '/new_dir';

        $result = $this->fs->createDirectory($path);

        $this->assertTrue($result);
        $this->assertDirectoryExists($path);
    }

    public function test_create_nested_directory() : void
    {
        // CreateDirectory only creates one level; parent must exist
        mkdir($this->tmpDir . '/a/b', 0o755, true);
        $path = $this->tmpDir . '/a/b/c';

        $result = $this->fs->createDirectory($path, 0o755);

        $this->assertTrue($result);
        $this->assertDirectoryExists($path);
    }

    public function test_create_existing_directory_returns_true() : void
    {
        $path = $this->tmpDir . '/already_here';
        mkdir($path);

        $result = $this->fs->createDirectory($path);

        $this->assertTrue($result);
    }

    public function test_delete_directory() : void
    {
        $path = $this->tmpDir . '/to_delete';
        mkdir($path);

        $result = $this->fs->deleteDirectory($path);

        $this->assertTrue($result);
        $this->assertDirectoryDoesNotExist($path);
    }

    public function test_clear_directory_removes_all_contents() : void
    {
        $dir = $this->tmpDir . '/to_clear';
        mkdir($dir);
        file_put_contents($dir . '/a.txt', 'a');
        file_put_contents($dir . '/b.txt', 'b');
        mkdir($dir . '/sub');
        file_put_contents($dir . '/sub/c.txt', 'c');

        $result = $this->fs->clearDirectory($dir);

        $this->assertTrue($result);
        // Directory itself still exists
        $this->assertDirectoryExists($dir);
        // Contents are gone
        $this->assertFileDoesNotExist($dir . '/a.txt');
        $this->assertFileDoesNotExist($dir . '/b.txt');
        $this->assertDirectoryDoesNotExist($dir . '/sub');
        $this->assertFileDoesNotExist($dir . '/sub/c.txt');
    }

    public function test_list_directory_returns_sorted_names() : void
    {
        $dir = $this->tmpDir . '/to_list';
        mkdir($dir);
        file_put_contents($dir . '/zebra.txt', 'z');
        file_put_contents($dir . '/alpha.txt', 'a');
        mkdir($dir . '/beta');

        $result = $this->fs->listDirectory($dir);

        $this->assertSame(['alpha.txt', 'beta', 'zebra.txt'], $result);
    }

    public function test_list_empty_directory_returns_empty_array() : void
    {
        $dir = $this->tmpDir . '/empty';
        mkdir($dir);

        $result = $this->fs->listDirectory($dir);

        $this->assertSame([], $result);
    }

    public function test_is_readable() : void
    {
        $path = $this->tmpDir . '/readable.txt';
        $this->fs->write($path, 'readable');

        $this->assertTrue($this->fs->isReadable($path));
    }

    public function test_is_readable_non_existing() : void
    {
        $this->assertFalse($this->fs->isReadable($this->tmpDir . '/nope.txt'));
    }

    public function test_is_writable_for_existing_file() : void
    {
        $path = $this->tmpDir . '/writable.txt';
        $this->fs->write($path, 'writable');

        $this->assertTrue($this->fs->isWritable($path));
    }

    public function test_is_writable_for_parent_directory() : void
    {
        $path = $this->tmpDir . '/new_file.txt';

        $this->assertTrue($this->fs->isWritable($path));
    }

    public function test_permissions_returns_octal() : void
    {
        $path = $this->tmpDir . '/perms.txt';
        $this->fs->write($path, 'perms');
        chmod($path, 0o644);

        $result = $this->fs->permissions($path);

        $this->assertSame(0o644, $result);
    }

    public function test_permissions_returns_null_for_non_existing() : void
    {
        $this->assertNull($this->fs->permissions($this->tmpDir . '/nope.txt'));
    }

    public function test_change_permissions() : void
    {
        $path = $this->tmpDir . '/chmod.txt';
        $this->fs->write($path, 'chmod');

        $result = $this->fs->changePermissions($path, 0o600);

        $this->assertTrue($result);
        $this->assertSame(0o600, $this->fs->permissions($path));
    }

    public function test_change_permissions_non_existing_returns_false() : void
    {
        $this->assertFalse($this->fs->changePermissions($this->tmpDir . '/nope.txt', 0o644));
    }

    public function test_read_non_existing_file_throws_file_not_found() : void
    {
        $this->expectException(FileNotFound::class);
        $this->fs->read($this->tmpDir . '/missing.txt');
    }

    public function test_copy_non_existing_source_throws_file_not_found() : void
    {
        $this->expectException(FileNotFound::class);
        $this->fs->copy($this->tmpDir . '/missing.txt', $this->tmpDir . '/dest.txt');
    }

    /* --- Failure Tests --- */

    public function test_move_non_existing_source_throws_file_not_found() : void
    {
        $this->expectException(FileNotFound::class);
        $this->fs->move($this->tmpDir . '/missing.txt', $this->tmpDir . '/dest.txt');
    }

    public function test_delete_directory_that_does_not_exist_throws() : void
    {
        $this->expectException(DirectoryNotFound::class);
        $this->fs->deleteDirectory($this->tmpDir . '/nonexistent_dir');
    }

    public function test_delete_non_empty_directory_returns_false() : void
    {
        $dir = $this->tmpDir . '/not_empty';
        mkdir($dir);
        file_put_contents($dir . '/file.txt', 'x');

        $result = $this->fs->deleteDirectory($dir);

        $this->assertFalse($result);
        $this->assertDirectoryExists($dir);
    }

    public function test_clear_non_existing_directory_throws() : void
    {
        $this->expectException(DirectoryNotFound::class);
        $this->fs->clearDirectory($this->tmpDir . '/no_dir');
    }

    public function test_list_non_existing_directory_throws() : void
    {
        $this->expectException(DirectoryNotFound::class);
        $this->fs->listDirectory($this->tmpDir . '/no_dir');
    }

    public function test_write_creates_parent_directories() : void
    {
        $path = $this->tmpDir . '/deep/nested/dir/file.txt';

        $result = $this->fs->write($path, 'nested content');

        $this->assertTrue($result);
        $this->assertFileExists($path);
        $this->assertSame('nested content', $this->fs->read($path));
    }

    public function test_binary_data_roundtrip() : void
    {
        $path   = $this->tmpDir . '/binary.bin';
        $binary = random_bytes(256);

        $this->fs->write($path, $binary);

        $this->assertSame($binary, $this->fs->read($path));
    }

    public function test_empty_file() : void
    {
        $path = $this->tmpDir . '/empty.txt';

        $this->fs->write($path, '');

        $this->assertSame('', $this->fs->read($path));
    }

    protected function setUp() : void
    {
        $this->tmpDir = sys_get_temp_dir() . '/avax_fs_test_' . uniqid();
        mkdir($this->tmpDir, 0o755, true);
        $this->fs = new Filesystem();
    }

    protected function tearDown() : void
    {
        $this->removeDirectoryRecursive($this->tmpDir);
    }

    /* --- Helpers --- */

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

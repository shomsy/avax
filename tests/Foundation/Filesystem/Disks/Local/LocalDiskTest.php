<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Disks\Local;

use Avax\Filesystem\Directories\DirectoryClearFailed;
use Avax\Filesystem\Disks\Local\LocalDisk;
use Avax\Filesystem\Files\FileNotFound;
use Avax\Tests\TestCase;

class LocalDiskTest extends TestCase
{
    private LocalDisk $disk;

    private string $testDir;

    private string $testFile;

    private string $copyFile;

    private string $movedFile;

    protected function setUp() : void
    {
        parent::setUp();
        $this->disk     = new LocalDisk();
        $this->testDir  = '/home/shomsy/projects/components/tests/fixtures/Filesystem/local_disk_test';
        $this->testFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/local_disk_test.txt';
        $this->copyFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/local_disk_copy.txt';
        $this->movedFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/local_disk_moved.txt';
        @mkdir(directory: $this->testDir, permissions: 0o755, recursive: true);
    }

    protected function tearDown() : void
    {
        if (is_dir(filename: $this->testDir)) {
            $this->disk->deleteDirectory(path: $this->testDir);
        }

        @unlink(filename: $this->testFile);
        @unlink(filename: $this->copyFile);
        @unlink(filename: $this->movedFile);
        parent::tearDown();
    }

    public function test_read_throws_exception_for_non_existent_file() : void
    {
        $this->expectException(exception: FileNotFound::class);

        $this->disk->read(path: '/ne postoji fajl.txt');
    }

    public function test_read_returns_file_contents() : void
    {
        file_put_contents(filename: $this->testFile, data: "sadrzaj\n");

        $result = $this->disk->read(path: $this->testFile);

        self::assertSame("sadrzaj\n", $result);
    }

    public function test_write_creates_file() : void
    {
        $result = $this->disk->write(path: $this->testFile, content: 'test');

        self::assertTrue(condition: $result);
        self::assertFileExists(filename: $this->testFile);
    }

    public function test_write_with_append() : void
    {
        file_put_contents(filename: $this->testFile, data: "linija1\n");

        $this->disk->write(path: $this->testFile, content: 'linija2', append: true);

        $content = file_get_contents(filename: $this->testFile);
        self::assertStringContainsString('linija1', $content);
        self::assertStringContainsString('linija2', $content);
    }

    public function test_copy_copies_file() : void
    {
        file_put_contents(filename: $this->testFile, data: "sadrzaj\n");

        $result = $this->disk->copy(source: $this->testFile, destination: $this->copyFile);

        self::assertTrue($result);
        self::assertFileExists($this->copyFile);
        self::assertSame("sadrzaj\n", file_get_contents(filename: $this->copyFile));
    }

    public function test_move_moves_file() : void
    {
        file_put_contents(filename: $this->testFile, data: "sadrzaj\n");

        $result = $this->disk->move(source: $this->testFile, destination: $this->movedFile);

        self::assertTrue($result);
        self::assertFileDoesNotExist($this->testFile);
        self::assertFileExists($this->movedFile);
    }

    public function test_delete_returns_true_for_non_existent_file() : void
    {
        $result = $this->disk->delete(path: '/ne postoji fajl.txt');

        self::assertTrue(condition: $result);
    }

    public function test_exists_returns_false_for_non_existent_path() : void
    {
        $result = $this->disk->exists(path: '/ne postoji put');

        self::assertFalse(condition: $result);
    }

    public function test_exists_returns_true_for_existing_path() : void
    {
        file_put_contents(filename: $this->testFile, data: "sadrzaj\n");

        $result = $this->disk->exists(path: $this->testFile);

        self::assertTrue(condition: $result);
    }

    public function test_create_directory() : void
    {
        $dir = $this->testDir . '/novi';

        $result = $this->disk->createDirectory(path: $dir);

        self::assertTrue(condition: $result);
        self::assertTrue(condition: is_dir(filename: $dir));
    }

    public function test_delete_directory() : void
    {
        file_put_contents(filename: $this->testDir . '/nested.txt', data: "sadrzaj\n");

        $result = $this->disk->deleteDirectory(path: $this->testDir);

        self::assertTrue(condition: $result);
        self::assertFalse(condition: is_dir(filename: $this->testDir));
    }

    public function test_delete_directory_returns_true_for_non_existent() : void
    {
        $result = $this->disk->deleteDirectory(path: '/ne postoji dir');

        self::assertTrue(condition: $result);
    }

    public function test_clear_throws_exception_for_non_directory() : void
    {
        $this->expectException(exception: DirectoryClearFailed::class);

        $this->disk->clear(path: $this->testFile);
    }

    public function test_clear_removes_contents() : void
    {
        file_put_contents(filename: $this->testDir . '/fajl.txt', data: "sadrzaj\n");

        $result = $this->disk->clear(path: $this->testDir);

        self::assertTrue(condition: $result);
        self::assertSame(['.', '..'], scandir(directory: $this->testDir));
    }

    public function test_is_writable() : void
    {
        $result = $this->disk->isWritable(path: $this->testDir);

        self::assertTrue(condition: $result);
    }

    public function test_set_permissions() : void
    {
        file_put_contents(filename: $this->testFile, data: "sadrzaj\n");

        $result = $this->disk->setPermissions(path: $this->testFile, permissions: 0o644);

        self::assertTrue(condition: $result);
    }

    public function test_has_permission() : void
    {
        file_put_contents(filename: $this->testFile, data: "sadrzaj\n");
        $this->disk->setPermissions(path: $this->testFile, permissions: 0o644);

        $result = $this->disk->hasPermission(path: $this->testFile, permissions: 0o644);

        self::assertTrue(condition: $result);
    }

    public function test_list_files() : void
    {
        file_put_contents(filename: $this->testDir . '/fajl1.txt', data: "sadrzaj1\n");
        file_put_contents(filename: $this->testDir . '/fajl2.txt', data: "sadrzaj2\n");

        $result = $this->disk->listFiles(path: $this->testDir);

        self::assertCount(2, $result);
    }

    public function test_list_files_returns_empty_for_non_existent_directory() : void
    {
        $result = $this->disk->listFiles(path: '/ne postoji dir');

        self::assertSame([], $result);
    }

    public function test_last_modified_returns_null_for_non_existent_file() : void
    {
        self::assertNull($this->disk->lastModified(path: '/ne postoji fajl.txt'));
    }

    public function test_last_modified_returns_timestamp_for_existing_file() : void
    {
        file_put_contents(filename: $this->testFile, data: "sadrzaj\n");

        self::assertIsInt($this->disk->lastModified(path: $this->testFile));
    }
}

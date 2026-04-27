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
        $this->disk = new LocalDisk();
        $this->testDir = '/home/shomsy/projects/components/tests/fixtures/Filesystem/local_disk_test';
        $this->testFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/local_disk_test.txt';
        $this->copyFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/local_disk_copy.txt';
        $this->movedFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/local_disk_moved.txt';
        @mkdir(directory: $this->testDir, permissions: 0755, recursive: true);
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

    public function testReadThrowsExceptionForNonExistentFile() : void
    {
        $this->expectException(exception: FileNotFound::class);

        $this->disk->read(path: '/ne postoji fajl.txt');
    }

    public function testReadReturnsFileContents() : void
    {
        file_put_contents(filename: $this->testFile, data: "sadrzaj\n");

        $result = $this->disk->read(path: $this->testFile);

        self::assertSame("sadrzaj\n", $result);
    }

    public function testWriteCreatesFile() : void
    {
        $result = $this->disk->write(path: $this->testFile, content: 'test');

        self::assertTrue(condition: $result);
        self::assertFileExists(filename: $this->testFile);
    }

    public function testWriteWithAppend() : void
    {
        file_put_contents(filename: $this->testFile, data: "linija1\n");

        $this->disk->write(path: $this->testFile, content: 'linija2', append: true);

        $content = file_get_contents(filename: $this->testFile);
        self::assertStringContainsString('linija1', $content);
        self::assertStringContainsString('linija2', $content);
    }

    public function testCopyCopiesFile() : void
    {
        file_put_contents(filename: $this->testFile, data: "sadrzaj\n");

        $result = $this->disk->copy(source: $this->testFile, destination: $this->copyFile);

        self::assertTrue($result);
        self::assertFileExists($this->copyFile);
        self::assertSame("sadrzaj\n", file_get_contents(filename: $this->copyFile));
    }

    public function testMoveMovesFile() : void
    {
        file_put_contents(filename: $this->testFile, data: "sadrzaj\n");

        $result = $this->disk->move(source: $this->testFile, destination: $this->movedFile);

        self::assertTrue($result);
        self::assertFileDoesNotExist($this->testFile);
        self::assertFileExists($this->movedFile);
    }

    public function testDeleteReturnsTrueForNonExistentFile() : void
    {
        $result = $this->disk->delete(path: '/ne postoji fajl.txt');

        self::assertTrue(condition: $result);
    }

    public function testExistsReturnsFalseForNonExistentPath() : void
    {
        $result = $this->disk->exists(path: '/ne postoji put');

        self::assertFalse(condition: $result);
    }

    public function testExistsReturnsTrueForExistingPath() : void
    {
        file_put_contents(filename: $this->testFile, data: "sadrzaj\n");

        $result = $this->disk->exists(path: $this->testFile);

        self::assertTrue(condition: $result);
    }

    public function testCreateDirectory() : void
    {
        $dir = $this->testDir . '/novi';

        $result = $this->disk->createDirectory(path: $dir);

        self::assertTrue(condition: $result);
        self::assertTrue(condition: is_dir(filename: $dir));
    }

    public function testDeleteDirectory() : void
    {
        file_put_contents(filename: $this->testDir . '/nested.txt', data: "sadrzaj\n");

        $result = $this->disk->deleteDirectory(path: $this->testDir);

        self::assertTrue(condition: $result);
        self::assertFalse(condition: is_dir(filename: $this->testDir));
    }

    public function testDeleteDirectoryReturnsTrueForNonExistent() : void
    {
        $result = $this->disk->deleteDirectory(path: '/ne postoji dir');

        self::assertTrue(condition: $result);
    }

    public function testClearThrowsExceptionForNonDirectory() : void
    {
        $this->expectException(exception: DirectoryClearFailed::class);

        $this->disk->clear(path: $this->testFile);
    }

    public function testClearRemovesContents() : void
    {
        file_put_contents(filename: $this->testDir . '/fajl.txt', data: "sadrzaj\n");

        $result = $this->disk->clear(path: $this->testDir);

        self::assertTrue(condition: $result);
        self::assertSame(['.', '..'], scandir(directory: $this->testDir));
    }

    public function testIsWritable() : void
    {
        $result = $this->disk->isWritable(path: $this->testDir);

        self::assertTrue(condition: $result);
    }

    public function testSetPermissions() : void
    {
        file_put_contents(filename: $this->testFile, data: "sadrzaj\n");

        $result = $this->disk->setPermissions(path: $this->testFile, permissions: 0644);

        self::assertTrue(condition: $result);
    }

    public function testHasPermission() : void
    {
        file_put_contents(filename: $this->testFile, data: "sadrzaj\n");
        $this->disk->setPermissions(path: $this->testFile, permissions: 0644);

        $result = $this->disk->hasPermission(path: $this->testFile, permissions: 0644);

        self::assertTrue(condition: $result);
    }

    public function testListFiles() : void
    {
        file_put_contents(filename: $this->testDir . '/fajl1.txt', data: "sadrzaj1\n");
        file_put_contents(filename: $this->testDir . '/fajl2.txt', data: "sadrzaj2\n");

        $result = $this->disk->listFiles(path: $this->testDir);

        self::assertCount(2, $result);
    }

    public function testListFilesReturnsEmptyForNonExistentDirectory() : void
    {
        $result = $this->disk->listFiles(path: '/ne postoji dir');

        self::assertSame([], $result);
    }

    public function testLastModifiedReturnsNullForNonExistentFile() : void
    {
        self::assertNull($this->disk->lastModified(path: '/ne postoji fajl.txt'));
    }

    public function testLastModifiedReturnsTimestampForExistingFile() : void
    {
        file_put_contents(filename: $this->testFile, data: "sadrzaj\n");

        self::assertIsInt($this->disk->lastModified(path: $this->testFile));
    }
}

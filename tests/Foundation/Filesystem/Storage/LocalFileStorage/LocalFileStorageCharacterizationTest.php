<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Storage\LocalFileStorage;

use Avax\Filesystem\Storage\LocalFileStorage;
use Avax\Filesystem\Storage\FileNotFoundException;
use Avax\Filesystem\Storage\FileWriteException;
use Avax\Filesystem\Exceptions\DirectoryCreationException;
use Avax\Filesystem\Exceptions\DirectoryDeletionException;
use Avax\Filesystem\Exceptions\FileDeleteException;
use PHPUnit\Framework\TestCase;
use Throwable;

class LocalFileStorageCharacterizationTest extends TestCase
{
    private LocalFileStorage $storage;
    private string $testDir;
    private string $testFile;
    private string $testFileTwo;

    protected function setUp() : void
    {
        parent::setUp();

        $this->storage = new LocalFileStorage();

        $this->testDir = '/home/shomsy/projects/components/tests/fixtures/Filesystem/test_directory';
        $this->testFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/test_file.txt';
        $this->testFileTwo = '/home/shomsy/projects/components/tests/fixtures/Filesystem/test_file_two.txt';

        @mkdir(directory: $this->testDir, permissions: 0755, recursive: true);
    }

    protected function tearDown() : void
    {
        @rmdir(directory: $this->testDir);
        @unlink(filename: $this->testFile);
        @unlink(filename: $this->testFileTwo);

        parent::tearDown();
    }

    public function testReadThrowsExceptionForNonExistentFile() : void
    {
        $this->expectException(exception: FileNotFoundException::class);
        $this->expectExceptionMessage(message: 'File not found: /nonexistent/file.txt');

        $this->storage->read(path: '/nonexistent/file.txt');
    }

    public function testReadThrowsExceptionForUnreadableFile() : void
    {
        if (posix_getuid() === 0) {
            $this->markTestSkipped(reason: 'Cannot test unreadable files as root');
        }

        $this->expectException(exception: FileNotFoundException::class);
        $this->expectExceptionMessage(message: 'File is not readable:');

        $this->storage->read(path: $this->testFile);
    }

    public function testReadReturnsFileContents() : void
    {
        file_put_contents(filename: $this->testFile, data: "test content\n");

        $result = $this->storage->read(path: $this->testFile);

        self::assertSame(expected: "test content\n", actual: $result);
    }

    public function testWriteCreatesParentDirectoryAutomatically() : void
    {
        $path = $this->testDir . '/subdir/nested/file.txt';

        $result = $this->storage->write(path: $path, content: 'content');

        self::assertTrue(condition: $result);
        self::assertFileExists(filename: $path);
    }

    public function testWriteAppendModeAddsContent() : void
    {
        file_put_contents(filename: $this->testFile, data: "line1\n");

        $this->storage->write(path: $this->testFile, content: 'line2', append: true);

        $content = file_get_contents(filename: $this->testFile);
        self::assertStringContainsString(haystack: $content, needdle: 'line1');
        self::assertStringContainsString(haystack: $content, needdle: 'line2');
    }

    public function testWriteOverwriteModeReplacesContent() : void
    {
        file_put_contents(filename: $this->testFile, data: "old content\n");

        $this->storage->write(path: $this->testFile, content: 'new content', append: false);

        $content = file_get_contents(filename: $this->testFile);
        self::assertSame(expected: "new content\n", actual: $content);
    }

    public function testWriteAddsNewlineAutomatically() : void
    {
        $this->storage->write(path: $this->testFile, content: 'content');

        $content = file_get_contents(filename: $this->testFile);

        self::assertStringEndsWith(haystack: $content, needdle: "\n");
    }

    public function testDeleteReturnsTrueForNonExistentFile() : void
    {
        $result = $this->storage->delete(path: '/nonexistent/file.txt');

        self::assertTrue(condition: $result);
    }

    public function testDeleteRemovesExistingFile() : void
    {
        file_put_contents(filename: $this->testFile, data: "content\n");

        $result = $this->storage->delete(path: $this->testFile);

        self::assertTrue(condition: $result);
        self::assertFileDoesNotExist(filename: $this->testFile);
    }

    public function testDeleteThrowsExceptionOnFailure() : void
    {
        $this->expectException(exception: FileDeleteException::class);
        $this->expectExceptionMessage(message: 'Failed to delete file:');

        $this->storage->delete(path: '/root/undeletable');
    }

    public function testExistsReturnsFalseForNonExistentPath() : void
    {
        $result = $this->storage->exists(path: '/nonexistent/path');

        self::assertFalse(condition: $result);
    }

    public function testExistsReturnsTrueForExistingPath() : void
    {
        file_put_contents(filename: $this->testFile, data: "content\n");

        $result = $this->storage->exists(path: $this->testFile);

        self::assertTrue(condition: $result);
    }

    public function testCreateDirectory() : void
    {
        $dir = $this->testDir . '/new_dir';

        $result = $this->storage->createDirectory(directory: $dir);

        self::assertTrue(condition: $result);
        self::assertTrue(condition: is_dir(filename: $dir));
    }

    public function testCreateDirectoryReturnsTrueIfAlreadyExists() : void
    {
        $result = $this->storage->createDirectory(directory: $this->testDir);

        self::assertTrue(condition: $result);
    }

    public function testCreateDirectoryThrowsExceptionOnFailure() : void
    {
        $this->expectException(exception: DirectoryCreationException::class);

        $this->storage->createDirectory(directory: '/root/impossible_dir');
    }

    public function testDeleteDirectoryReturnsTrueForNonExistentDirectory() : void
    {
        $result = $this->storage->deleteDirectory(directory: '/nonexistent/dir');

        self::assertTrue(condition: $result);
    }

    public function testDeleteDirectoryRemovesExistingDirectory() : void
    {
        $result = $this->storage->deleteDirectory(directory: $this->testDir);

        self::assertTrue(condition: $result);
        self::assertFalse(condition: is_dir(filename: $this->testDir));
    }

    public function testDeleteDirectoryThrowsExceptionOnFailure() : void
    {
        $this->expectException(exception: DirectoryDeletionException::class);

        $this->storage->deleteDirectory(directory: '/root/impossible');
    }

    public function testClearThrowsExceptionForNonDirectory() : void
    {
        $this->expectException(exception: Throwable::class);
        $this->expectExceptionMessage(message: 'Not a directory:');

        $this->storage->clear(directory: $this->testFile);
    }

    public function testClearRemovesDirectoryContents() : void
    {
        file_put_contents(filename: $this->testDir . '/file1.txt', data: "content1\n");
        file_put_contents(filename: $this->testDir . '/file2.txt', data: "content2\n");

        $result = $this->storage->clear(directory: $this->testDir);

        self::assertTrue(condition: $result);
        self::assertCount(expected: 0, haystack: scandir(directory: $this->testDir));
    }

    public function testIsWritableReturnsTrueForWritablePath() : void
    {
        $result = $this->storage->isWritable(path: $this->testDir);

        self::assertTrue(condition: $result);
    }

    public function testSetPermissions() : void
    {
        $this->storage->write(path: $this->testFile, content: 'content');

        $result = $this->storage->setPermissions(path: $this->testFile, permissions: 0644);

        self::assertTrue(condition: $result);
    }

    public function testSetPermissionsThrowsExceptionForNonExistentPath() : void
    {
        $this->expectException(exception: Throwable::class);
        $this->expectExceptionMessage(message: 'Path does not exist:');

        $this->storage->setPermissions(path: '/nonexistent/path', permissions: 0644);
    }

    public function testHasPermissionReturnsFalseForNonExistentPath() : void
    {
        $result = $this->storage->hasPermission(path: '/nonexistent/path', permissions: 0755);

        self::assertFalse(condition: $result);
    }

    public function testHasPermissionComparesPermissions() : void
    {
        $this->storage->write(path: $this->testFile, content: 'content');
        $this->storage->setPermissions(path: $this->testFile, permissions: 0644);

        $result = $this->storage->hasPermission(path: $this->testFile, permissions: 0644);

        self::assertTrue(condition: $result);
    }

    public function testListFilesReturnsEmptyArrayForNonExistentDirectory() : void
    {
        $result = $this->storage->listFiles(directory: '/nonexistent/dir');

        self::assertSame(expected: [], actual: $result);
    }

    public function testListFilesReturnsEmptyArrayForUnreadableDirectory() : void
    {
        if (posix_getuid() === 0) {
            $this->markTestSkipped(reason: 'Cannot test unreadable directories as root');
        }

        $result = $this->storage->listFiles(directory: $this->testDir);

        self::assertSame(expected: [], actual: $result);
    }

    public function testListFilesReturnsFilePaths() : void
    {
        file_put_contents(filename: $this->testDir . '/file1.txt', data: "content1\n");
        file_put_contents(filename: $this->testDir . '/file2.txt', data: "content2\n");

        $result = $this->storage->listFiles(directory: $this->testDir);

        self::assertCount(expected: 2, haystack: $result);
    }
}
<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Storage\Filesystem;

use Avax\Filesystem\Storage\Filesystem;
use Avax\Filesystem\Storage\LocalFileStorage;
use Exception;
use PHPUnit\Framework\TestCase;

class StorageFilesystemCharacterizationTest extends TestCase
{
    private LocalFileStorage $storage;
    private Filesystem $filesystem;
    private string $testDir;
    private string $testFile;

    protected function setUp() : void
    {
        parent::setUp();

        $this->storage = new LocalFileStorage();
        $this->filesystem = new Filesystem(fileStorage: $this->storage);

        $this->testDir = '/home/shomsy/projects/components/tests/fixtures/Filesystem/storage_test_dir';
        $this->testFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/storage_test_file.txt';

        @mkdir(directory: $this->testDir, permissions: 0755, recursive: true);
    }

    protected function tearDown() : void
    {
        @rmdir(directory: $this->testDir);
        @unlink(filename: $this->testFile);

        parent::tearDown();
    }

    public function testDiskThrowsExceptionForUnsupportedDriver() : void
    {
        $this->expectException(exception: Exception::class);
        $this->expectExceptionMessage(message: 'Unsupported disk driver:');

        $this->filesystem->disk(name: 'unknown');
    }

    public function testReadDelegatesToStorage() : void
    {
        file_put_contents(filename: $this->testFile, data: "test content\n");

        $result = $this->filesystem->read(path: $this->testFile);

        self::assertSame(expected: "test content\n", actual: $result);
    }

    public function testWriteDelegatesToStorage() : void
    {
        $result = $this->filesystem->write(path: $this->testFile, content: 'test');

        self::assertTrue(condition: $result);
    }

    public function testDeleteDelegatesToStorage() : void
    {
        file_put_contents(filename: $this->testFile, data: "content\n");

        $result = $this->filesystem->delete(path: $this->testFile);

        self::assertTrue(condition: $result);
    }

    public function testExistsDelegatesToStorage() : void
    {
        file_put_contents(filename: $this->testFile, data: "content\n");

        $result = $this->filesystem->exists(path: $this->testFile);

        self::assertTrue(condition: $result);
    }

    public function testCreateDirectoryDelegatesToStorage() : void
    {
        $result = $this->filesystem->createDirectory(directory: $this->testDir . '/new');

        self::assertTrue(condition: $result);
    }

    public function testDeleteDirectoryDelegatesToStorage() : void
    {
        $result = $this->filesystem->deleteDirectory(directory: $this->testDir);

        self::assertTrue(condition: $result);
    }

    public function testSetPermissionsDelegatesToStorage() : void
    {
        $this->filesystem->write(path: $this->testFile, content: 'content');

        $result = $this->filesystem->setPermissions(path: $this->testFile, permissions: 0644);

        self::assertTrue(condition: $result);
    }

    public function testHasPermissionDelegatesToStorage() : void
    {
        $this->filesystem->write(path: $this->testFile, content: 'content');
        $this->filesystem->setPermissions(path: $this->testFile, permissions: 0644);

        $result = $this->filesystem->hasPermission(path: $this->testFile, permissions: 0644);

        self::assertTrue(condition: $result);
    }

    public function testClearDelegatesToStorage() : void
    {
        file_put_contents(filename: $this->testDir . '/file.txt', data: "content\n");

        $result = $this->filesystem->clear(directory: $this->testDir);

        self::assertTrue(condition: $result);
    }

    public function testIsWritableDelegatesToStorage() : void
    {
        $result = $this->filesystem->isWritable(path: $this->testDir);

        self::assertTrue(condition: $result);
    }

    public function testListFilesDelegatesToStorage() : void
    {
        file_put_contents(filename: $this->testDir . '/file1.txt', data: "content\n");

        $result = $this->filesystem->listFiles(directory: $this->testDir);

        self::assertCount(expected: 1, haystack: $result);
    }
}
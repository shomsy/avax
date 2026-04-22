<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\LocalFileService;

use Avax\Filesystem\LocalFileService;
use PHPUnit\Framework\TestCase;

class LocalFileServiceCharacterizationTest extends TestCase
{
    private LocalFileService $service;
    private string $testDir;
    private string $testFile;

    protected function setUp() : void
    {
        parent::setUp();

        $this->service = new LocalFileService();
        $this->testDir = '/home/shomsy/projects/components/tests/fixtures/Filesystem/service_test_dir';
        $this->testFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/service_test_file.txt';

        @mkdir(directory: $this->testDir, permissions: 0755, recursive: true);
    }

    protected function tearDown() : void
    {
        @rmdir(directory: $this->testDir);
        @unlink(filename: $this->testFile);

        parent::tearDown();
    }

    public function testSetPermissions() : void
    {
        $this->service->createFile(path: $this->testFile);

        $result = $this->service->setPermissions(path: $this->testFile, permissions: 0644);

        self::assertTrue(condition: $result);
    }

    public function testIsWritable() : void
    {
        $result = $this->service->isWritable(path: $this->testDir);

        self::assertTrue(condition: $result);
    }

    public function testCreateFileCreatesNewFile() : void
    {
        $result = $this->service->createFile(path: $this->testFile);

        self::assertTrue(condition: $result);
        self::assertFileExists(filename: $this->testFile);
    }

    public function testCreateFileReturnsTrueIfFileExists() : void
    {
        file_put_contents(filename: $this->testFile, data: "existing\n");

        $result = $this->service->createFile(path: $this->testFile);

        self::assertTrue(condition: $result);
    }

    public function testFileExistsReturnsFalseForNonExistentFile() : void
    {
        $result = $this->service->fileExists(path: '/nonexistent/file.txt');

        self::assertFalse(condition: $result);
    }

    public function testFileExistsReturnsTrueForExistingFile() : void
    {
        file_put_contents(filename: $this->testFile, data: "content\n");

        $result = $this->service->fileExists(path: $this->testFile);

        self::assertTrue(condition: $result);
    }

    public function testAppendToFile() : void
    {
        file_put_contents(filename: $this->testFile, data: "line1\n");

        $result = $this->service->appendToFile(path: $this->testFile, content: 'line2');

        self::assertTrue(condition: $result);
    }

    public function testAppendToFileCreatesParentDirectory() : void
    {
        $file = $this->testDir . '/subdir/newfile.txt';

        $result = $this->service->appendToFile(path: $file, content: 'content');

        self::assertTrue(condition: $result);
        self::assertFileExists(filename: $file);
    }

    public function testAppendToFileReturnsFalseIfDirectoryCreationFails() : void
    {
        $result = $this->service->appendToFile(path: '/root/impossible/file.txt', content: 'content');

        self::assertFalse(condition: $result);
    }

    public function testIsDirectoryReturnsFalseForFile() : void
    {
        file_put_contents(filename: $this->testFile, data: "content\n");

        $result = $this->service->isDirectory(path: $this->testFile);

        self::assertFalse(condition: $result);
    }

    public function testIsDirectoryReturnsTrueForDirectory() : void
    {
        $result = $this->service->isDirectory(path: $this->testDir);

        self::assertTrue(condition: $result);
    }

    public function testCreateDirectory() : void
    {
        $dir = $this->testDir . '/new_subdir';

        $result = $this->service->createDirectory(path: $dir, permissions: 0755);

        self::assertTrue(condition: $result);
        self::assertTrue(condition: is_dir(filename: $dir));
    }

    public function testCreateDirectoryReturnsTrueIfExists() : void
    {
        $result = $this->service->createDirectory(path: $this->testDir, permissions: 0755);

        self::assertTrue(condition: $result);
    }
}
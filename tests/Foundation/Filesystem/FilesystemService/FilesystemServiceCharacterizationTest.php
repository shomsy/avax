<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\FilesystemService;

use Avax\Filesystem\FilesystemService;
use PHPUnit\Framework\TestCase;

class FilesystemServiceCharacterizationTest extends TestCase
{
    private FilesystemService $service;
    private string $testDir;
    private string $testFile;

    protected function setUp() : void
    {
        parent::setUp();

        $this->service = new FilesystemService();
        $this->testDir = '/home/shomsy/projects/components/tests/fixtures/Filesystem/fs_service_test_dir';
        $this->testFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/fs_service_test_file.txt';

        @mkdir(directory: $this->testDir, permissions: 0755, recursive: true);
    }

    protected function tearDown() : void
    {
        @rmdir(directory: $this->testDir);
        @unlink(filename: $this->testFile);

        parent::tearDown();
    }

    public function testWriteFileCreatesFile() : void
    {
        $this->service->writeFile(fileName: $this->testFile, content: 'test content');

        self::assertFileExists(filename: $this->testFile);
    }

    public function testWriteFileOverwritesExistingFile() : void
    {
        file_put_contents(filename: $this->testFile, data: "old content");

        $this->service->writeFile(fileName: $this->testFile, content: 'new content');

        $content = file_get_contents(filename: $this->testFile);
        self::assertSame(expected: 'new content', actual: $content);
    }

    public function testEnsureDirectoryIsWritableCreatesDirectory() : void
    {
        $dir = '/home/shomsy/projects/components/tests/fixtures/Filesystem/fs_new_dir';

        try {
            $this->service->ensureDirectoryIsWritable(directory: $dir);
        } finally {
            @rmdir(directory: $dir);
        }

        self::assertTrue(condition: is_dir(filename: $dir));
    }

    public function testEnsureDirectoryIsWritableSetsPermissionsTo0755() : void
    {
        $dir = '/home/shomsy/projects/components/tests/fixtures/Filesystem/fs_perm_dir';

        try {
            $this->service->ensureDirectoryIsWritable(directory: $dir);
        } finally {
            @rmdir(directory: $dir);
        }

        $perms = fileperms(filename: $dir) & 0777;
        self::assertSame(expected: 0755, actual: $perms);
    }

    public function testEnsureDirectoryIsWritableDoesNotChangeExistingWritableDirectory() : void
    {
        $this->service->ensureDirectoryIsWritable(directory: $this->testDir);

        $perms = fileperms(filename: $this->testDir) & 0777;
        self::assertSame(expected: 0755, actual: $perms);
    }
}
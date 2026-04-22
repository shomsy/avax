<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\DirectoryInitializer;

use Avax\Filesystem\DirectoryInitializer;
use Avax\Filesystem\LocalFileService;
use Exception;
use PHPUnit\Framework\TestCase;

class DirectoryInitializerCharacterizationTest extends TestCase
{
    private string $testDir;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testDir = '/home/shomsy/projects/components/tests/fixtures/Filesystem/init_test_dir';

        @mkdir(directory: $this->testDir, permissions: 0755, recursive: true);
    }

    protected function tearDown() : void
    {
        @rmdir(directory: $this->testDir);

        parent::tearDown();
    }

    public function testConstructorCreatesDirectoryIfNotExists() : void
    {
        $dir = '/home/shomsy/projects/components/tests/fixtures/Filesystem/init_new_dir';

        try {
            new DirectoryInitializer(
                directoryPath: $dir,
                fileService: new LocalFileService()
            );
        } finally {
            @rmdir(directory: $dir);
        }

        self::assertTrue(condition: is_dir(filename: $dir));
    }

    public function testConstructorSetsPermissions() : void
    {
        $dir = '/home/shomsy/projects/components/tests/fixtures/Filesystem/init_perm_dir';

        try {
            new DirectoryInitializer(
                directoryPath: $dir,
                fileService: new LocalFileService()
            );
        } finally {
            @rmdir(directory: $dir);
        }

        $perms = fileperms(filename: $dir) & 0777;
        self::assertSame(expected: 0755, actual: $perms);
    }

    public function testConstructorThrowsExceptionIfDirectoryCreationFails() : void
    {
        $this->expectException(exception: Exception::class);
        $this->expectExceptionMessage(message: 'Failed to create directory at');

        new DirectoryInitializer(
            directoryPath: '/root/impossible_dir',
            fileService: new LocalFileService()
        );
    }

    public function testConstructorThrowsExceptionIfDirectoryNotWritable() : void
    {
        if (posix_getuid() === 0) {
            $this->markTestSkipped(reason: 'Cannot test writability as root');
        }

        $this->expectException(exception: Exception::class);
        $this->expectExceptionMessage(message: 'Unable to make the directory writable');

        new DirectoryInitializer(
            directoryPath: '/root/impossible_write',
            fileService: new LocalFileService()
        );
    }
}
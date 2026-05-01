<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Directories;

use Avax\Filesystem\Directories\DeleteDirectory;
use Avax\Filesystem\Disks\Local\LocalDisk;
use Avax\Tests\TestCase;

class DeleteDirectoryTest extends TestCase
{
    private LocalDisk $disk;

    private string $testDir;

    protected function setUp() : void
    {
        parent::setUp();
        $this->disk = new LocalDisk;
        $this->testDir = '/home/shomsy/projects/components/tests/fixtures/Filesystem/delete_dir_test';
        @mkdir(directory: $this->testDir, permissions: 0o755, recursive: true);
    }

    protected function tearDown() : void
    {
        if (is_dir(filename: $this->testDir)) {
            $this->disk->deleteDirectory(path: $this->testDir);
        }

        parent::tearDown();
    }

    public function test_execute_returns_true_for_non_existent_directory() : void
    {
        $result = new DeleteDirectory(disk: $this->disk)->execute(path: '/ne postoji dir');

        self::assertTrue(condition: $result);
    }

    public function test_execute_deletes_existing_directory() : void
    {
        file_put_contents(filename: $this->testDir . '/nested.txt', data: "content\n");

        $result = new DeleteDirectory(disk: $this->disk)->execute(path: $this->testDir);

        self::assertTrue(condition: $result);
        self::assertFalse(condition: is_dir(filename: $this->testDir));
    }
}
